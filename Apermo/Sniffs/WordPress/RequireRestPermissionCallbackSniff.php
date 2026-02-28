<?php
/**
 * Require permission_callback in register_rest_route() calls.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

/**
 * Flags register_rest_route() calls that do not include a
 * permission_callback in the route arguments array.
 *
 * Missing permission callbacks are the #1 REST API security hole —
 * endpoints default to public access when omitted.
 *
 * Error codes:
 * - Missing: no permission_callback key found in the args array
 */
class RequireRestPermissionCallbackSniff implements Sniff {

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

		if ( $funcName !== 'register_rest_route' ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$params = PassedParameters::getParameters( $phpcsFile, $stackPtr );

		// register_rest_route( $namespace, $route, $args, $override )
		// The args parameter is the 3rd positional argument.
		$argsParam = PassedParameters::getParameterFromStack( $params, 3, 'args' );
		if ( $argsParam === false ) {
			$phpcsFile->addError(
				'register_rest_route() called without route arguments; include a permission_callback',
				$stackPtr,
				'Missing',
			);
			return;
		}

		if ( $this->containsPermissionCallback( $phpcsFile, $argsParam ) ) {
			return;
		}

		$phpcsFile->addError(
			'register_rest_route() args must include a permission_callback',
			$stackPtr,
			'Missing',
		);
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
	 * Check if the args parameter contains a 'permission_callback' key.
	 *
	 * Scans the token range of the args parameter for the string
	 * 'permission_callback' used as an array key (followed by =>).
	 * Non-literal args (variables, function calls) are assumed
	 * correct since they cannot be verified statically.
	 *
	 * @param File                                    $phpcsFile The file being scanned.
	 * @param array{start: int, end: int, raw: string} $param     Parameter info from PassedParameters.
	 *
	 * @return bool
	 */
	private function containsPermissionCallback( File $phpcsFile, array $param ): bool {
		$tokens = $phpcsFile->getTokens();

		// If the arg is a variable or function call, we can't statically
		// verify the contents — give the benefit of the doubt.
		if ( ! $this->isArrayLiteral( $phpcsFile, $param ) ) {
			return true;
		}

		// Scan tokens in the args range for 'permission_callback' => ...
		for ( $i = $param['start']; $i <= $param['end']; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_CONSTANT_ENCAPSED_STRING ) {
				continue;
			}

			$keyContent = $this->stripQuotes( $tokens[ $i ]['content'] );
			if ( $keyContent !== 'permission_callback' ) {
				continue;
			}

			// Verify it's used as an array key (followed by =>).
			$nextNonWs = $phpcsFile->findNext( T_WHITESPACE, $i + 1, $param['end'] + 1, true );
			if ( $nextNonWs !== false && $tokens[ $nextNonWs ]['code'] === T_DOUBLE_ARROW ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the parameter contains an array literal ([ or array().
	 *
	 * @param File                                    $phpcsFile The file being scanned.
	 * @param array{start: int, end: int, raw: string} $param     Parameter info.
	 *
	 * @return bool
	 */
	private function isArrayLiteral( File $phpcsFile, array $param ): bool {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $param['start']; $i <= $param['end']; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			return $tokens[ $i ]['code'] === T_OPEN_SHORT_ARRAY
				|| $tokens[ $i ]['code'] === T_ARRAY;
		}

		return false;
	}

	/**
	 * Strip surrounding quotes from a string token.
	 *
	 * @param string $content The token content.
	 *
	 * @return string The unquoted string.
	 */
	private function stripQuotes( string $content ): string {
		if ( strlen( $content ) >= 2
			&& ( $content[0] === '\'' || $content[0] === '"' )
		) {
			return substr( $content, 1, -1 );
		}

		return $content;
	}
}
