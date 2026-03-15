<?php
declare(strict_types=1);

/**
 * Enforce correct function qualification in namespaced code.
 *
 * @package Apermo\Sniffs\Namespaces
 */

namespace Apermo\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Requires FQ for PHP native functions (\strlen) and prohibits FQ
 * for non-native functions (WordPress, userland) in namespaced code.
 * Auto-fixable.
 */
class GlobalFunctionQualificationSniff implements Sniff {

	/**
	 * Tokens that indicate the T_STRING is not a function call.
	 *
	 * @var list<int|string>
	 */
	private const SKIP_PREV_TOKENS = [
		T_OBJECT_OPERATOR,
		T_NULLSAFE_OBJECT_OPERATOR,
		T_DOUBLE_COLON,
		T_FUNCTION,
		T_NEW,
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
	 * @return void|int
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Only apply in namespaced files.
		if ( ! $this->isInNamespace( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$name = $tokens[ $stackPtr ]['content'];

		// Must be a function call (followed by open parenthesis).
		$next = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		if ( $next === false || $tokens[ $next ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Check previous token for context.
		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		if ( $prev === false ) {
			return;
		}

		$prev_code = $tokens[ $prev ]['code'];

		// Skip method calls, function declarations, class instantiation.
		if ( \in_array( $prev_code, self::SKIP_PREV_TOKENS, true ) ) {
			return;
		}

		// Determine if fully qualified.
		$is_fq    = false;
		$sep_ptr  = 0;
		if ( $prev_code === T_NS_SEPARATOR ) {
			// Check if root-qualified (\func) or namespace-qualified (Foo\func).
			$before_sep = $phpcsFile->findPrevious( T_WHITESPACE, $prev - 1, null, true );
			if ( $before_sep !== false
				&& \in_array( $tokens[ $before_sep ]['code'], [ T_STRING, T_NAMESPACE ], true )
			) {
				// Namespace-qualified call — not our concern.
				return;
			}
			$is_fq   = true;
			$sep_ptr = $prev;
		}

		$is_native = $this->isPhpNativeFunction( $name );

		if ( $is_native && ! $is_fq ) {
			$fix = $phpcsFile->addFixableError(
				'PHP native function %s() must be fully qualified as \%s() in namespaced code',
				$stackPtr,
				'NativeNotFullyQualified',
				[ $name, $name ],
			);
			if ( $fix ) {
				$phpcsFile->fixer->addContentBefore( $stackPtr, '\\' );
			}
			return;
		}

		if ( ! $is_native && $is_fq ) {
			$fix = $phpcsFile->addFixableError(
				'Non-native function \%s() should not be fully qualified',
				$stackPtr,
				'NonNativeFullyQualified',
				[ $name ],
			);
			if ( $fix ) {
				$phpcsFile->fixer->replaceToken( $sep_ptr, '' );
			}
		}
	}

	/**
	 * Check if the token is inside a namespace declaration.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return bool
	 */
	private function isInNamespace( File $phpcsFile, int $stackPtr ): bool {
		$tokens    = $phpcsFile->getTokens();
		$ns_token  = $phpcsFile->findPrevious( T_NAMESPACE, $stackPtr - 1 );

		if ( $ns_token === false ) {
			return false;
		}

		// Verify it's a namespace declaration (next non-whitespace is a name),
		// not a relative namespace reference like namespace\func().
		$after = $phpcsFile->findNext( T_WHITESPACE, $ns_token + 1, null, true );
		return $after !== false && $tokens[ $after ]['code'] === T_STRING;
	}

	/**
	 * Check if a function is a PHP native (internal) function.
	 *
	 * @param string $name The function name.
	 *
	 * @return bool
	 */
	private function isPhpNativeFunction( string $name ): bool {
		if ( ! \function_exists( $name ) ) {
			return false;
		}

		try {
			$ref = new \ReflectionFunction( $name );
			return $ref->isInternal();
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}
}
