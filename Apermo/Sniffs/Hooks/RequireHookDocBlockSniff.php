<?php
/**
 * Require PHPDoc blocks before WordPress hook invocations.
 *
 * @package Apermo\Sniffs\Hooks
 */

namespace Apermo\Sniffs\Hooks;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

/**
 * Enforces that WordPress hook invocations (do_action, apply_filters, etc.)
 * are preceded by a PHPDoc block documenting the hook's parameters and,
 * for filters, the expected return value.
 */
class RequireHookDocBlockSniff implements Sniff {

	/**
	 * Hook functions to check.
	 *
	 * @var array<string, string> Maps function name to type: 'action' or 'filter'.
	 */
	private const HOOK_FUNCTIONS = [
		'do_action'                  => 'action',
		'do_action_ref_array'        => 'action',
		'do_action_deprecated'       => 'action',
		'apply_filters'              => 'filter',
		'apply_filters_ref_array'    => 'filter',
		'apply_filters_deprecated'   => 'filter',
	];

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_STRING ];
	}

	/**
	 * Processes a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens   = $phpcsFile->getTokens();
		$funcName = strtolower( $tokens[ $stackPtr ]['content'] );

		if ( ! isset( self::HOOK_FUNCTIONS[ $funcName ] ) ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$hookType     = self::HOOK_FUNCTIONS[ $funcName ];
		$statementStart = $phpcsFile->findStartOfStatement( $stackPtr );
		$docBlock     = $this->findDocBlock( $phpcsFile, $statementStart );

		if ( $docBlock === null ) {
			$phpcsFile->addWarning(
				'Hook invocation %s() must be preceded by a PHPDoc block',
				$stackPtr,
				'Missing',
				[ $tokens[ $stackPtr ]['content'] ]
			);
			return;
		}

		$this->validateDocBlock( $phpcsFile, $stackPtr, $docBlock, $hookType, $funcName );
	}

	/**
	 * Checks whether a T_STRING token is a genuine function call.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the T_STRING token.
	 *
	 * @return bool
	 */
	private function isFunctionCall( File $phpcsFile, int $stackPtr ): bool {
		$tokens = $phpcsFile->getTokens();

		$next = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		if ( $next === false || $tokens[ $next ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		if ( $prev === false ) {
			return true;
		}

		$prevCode = $tokens[ $prev ]['code'];

		if ( in_array( $prevCode, [ T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR ], true ) ) {
			return false;
		}

		if ( $prevCode === T_FUNCTION ) {
			return false;
		}

		return true;
	}

	/**
	 * Search backward from statement start for a doc block.
	 *
	 * @param File $phpcsFile      The file being scanned.
	 * @param int  $statementStart The position of the statement start.
	 *
	 * @return array{open: int, close: int}|null Doc block token boundaries, or null.
	 */
	private function findDocBlock( File $phpcsFile, int $statementStart ): ?array {
		$tokens = $phpcsFile->getTokens();

		for ( $i = ( $statementStart - 1 ); $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_CLOSE_TAG ) {
				$openPtr = $tokens[ $i ]['comment_opener'] ?? null;

				if ( $openPtr === null ) {
					return null;
				}

				return [ 'open' => $openPtr, 'close' => $i ];
			}

			return null;
		}

		return null;
	}

	/**
	 * Validate the doc block has required @param and @return tags.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The hook function token position.
	 * @param array  $docBlock  Doc block boundaries with 'open' and 'close' keys.
	 * @param string $hookType  'action' or 'filter'.
	 * @param string $funcName  The lowercased hook function name.
	 *
	 * @return void
	 */
	private function validateDocBlock( File $phpcsFile, int $stackPtr, array $docBlock, string $hookType, string $funcName ): void {
		$tokens     = $phpcsFile->getTokens();
		$paramCount = 0;
		$hasReturn  = false;

		for ( $i = $docBlock['open']; $i <= $docBlock['close']; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_TAG ) {
				$tagContent = strtolower( $tokens[ $i ]['content'] );
				if ( $tagContent === '@param' ) {
					$paramCount++;
				} elseif ( $tagContent === '@return' ) {
					$hasReturn = true;
				}
			}
		}

		$hookArgs = $this->countHookArguments( $phpcsFile, $stackPtr, $funcName );

		if ( $hookArgs > 0 && $paramCount === 0 ) {
			$phpcsFile->addWarning(
				'Hook doc block is missing @param tags for the hook arguments',
				$stackPtr,
				'MissingParam'
			);
		}

		if ( $hookType === 'filter' && ! $hasReturn ) {
			$phpcsFile->addWarning(
				'Filter doc block must include a @return tag describing the filtered value',
				$stackPtr,
				'MissingReturn'
			);
		}
	}

	/**
	 * Count hook arguments beyond the hook name.
	 *
	 * For _ref_array and _deprecated variants, the second arg is an
	 * args array. Empty array literals ([] or array()) are treated
	 * as zero arguments.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The hook function token position.
	 * @param string $funcName  The lowercased hook function name.
	 *
	 * @return int Number of hook arguments (beyond the hook name).
	 */
	private function countHookArguments( File $phpcsFile, int $stackPtr, string $funcName ): int {
		$params     = PassedParameters::getParameters( $phpcsFile, $stackPtr );
		$paramCount = count( $params );

		if ( str_ends_with( $funcName, '_ref_array' ) || str_ends_with( $funcName, '_deprecated' ) ) {
			// Both variants pass hook args as an array in parameter 2.
			$argsParam = PassedParameters::getParameterFromStack( $params, 2, 'args' );
			if ( $argsParam === false ) {
				return 0;
			}

			$raw = trim( $argsParam['raw'] );
			if ( $raw === '[]' || strcasecmp( $raw, 'array()' ) === 0 ) {
				return 0;
			}

			return 1;
		}

		// Standard: arg 1 = hook name, rest are hook arguments.
		return max( 0, $paramCount - 1 );
	}
}
