<?php
declare(strict_types=1);

/**
 * Detects genuine function calls vs. definitions and method calls.
 *
 * @package Apermo\Sniffs\Helpers
 */

namespace Apermo\Sniffs\Helpers;

use PHP_CodeSniffer\Files\File;

/**
 * Provides a reusable isFunctionCall() check for sniffs that listen on
 * T_STRING and need to distinguish global function calls from method calls,
 * static calls, nullsafe calls, and function definitions.
 */
trait FunctionCallDetectorTrait {

	/**
	 * Checks whether a T_STRING token is a genuine function call.
	 *
	 * Returns false for:
	 * - Method calls ($obj->func(), Class::func(), $obj?->func())
	 * - Function definitions (function func())
	 * - Non-call usage (no parenthesis following the token)
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
}
