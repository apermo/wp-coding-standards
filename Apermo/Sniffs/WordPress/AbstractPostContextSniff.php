<?php
/**
 * Abstract base for post-context sniffs.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Shared utilities for sniffs that check implicit global $post access.
 *
 * Provides scope detection and argument counting helpers used by
 * GlobalPostAccessSniff and ImplicitPostFunctionSniff.
 */
abstract class AbstractPostContextSniff implements Sniff {

	/**
	 * Checks whether the token is inside a function, method, closure, or arrow function.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return bool
	 */
	protected function isInsideFunctionScope( File $phpcsFile, int $stackPtr ): bool {
		if ( $phpcsFile->hasCondition( $stackPtr, [ T_FUNCTION, T_CLOSURE ] ) ) {
			return true;
		}

		// T_FN (arrow functions) are not tracked in PHPCS's conditions
		// system because they lack curly braces. Walk backward to check
		// if this token falls inside an arrow function's scope.
		return $this->isInsideArrowFunction( $phpcsFile, $stackPtr );
	}

	/**
	 * Checks whether the token is inside an arrow function scope.
	 *
	 * PHPCS does not add T_FN to the conditions array, so we search
	 * backward for a T_FN token whose scope range contains $stackPtr.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return bool
	 */
	private function isInsideArrowFunction( File $phpcsFile, int $stackPtr ): bool {
		$tokens = $phpcsFile->getTokens();

		$prev = $phpcsFile->findPrevious( T_FN, $stackPtr - 1 );
		while ( $prev !== false ) {
			if ( isset( $tokens[ $prev ]['scope_closer'] )
				&& $tokens[ $prev ]['scope_closer'] >= $stackPtr
			) {
				return true;
			}

			$prev = $phpcsFile->findPrevious( T_FN, $prev - 1 );
		}

		return false;
	}

	/**
	 * Checks whether a T_STRING token is a genuine function call.
	 *
	 * Returns false for method calls (->foo(), ::foo(), ?->foo()),
	 * function declarations, and non-call references.
	 * Allows namespace-prefixed calls (\get_the_title()).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the T_STRING token.
	 *
	 * @return bool
	 */
	protected function isFunctionCall( File $phpcsFile, int $stackPtr ): bool {
		$tokens = $phpcsFile->getTokens();

		// Must be followed by an opening parenthesis.
		$next = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		if ( $next === false || $tokens[ $next ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		// Check the token before the function name.
		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		if ( $prev === false ) {
			return true;
		}

		$prevCode = $tokens[ $prev ]['code'];

		// Method call: ->, ::, ?->
		if ( in_array( $prevCode, [ T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR ], true ) ) {
			return false;
		}

		// Function/method declaration.
		if ( $prevCode === T_FUNCTION ) {
			return false;
		}

		// Namespace prefix (\get_the_title) — still a function call.
		if ( $prevCode === T_NS_SEPARATOR ) {
			return true;
		}

		return true;
	}

	/**
	 * Counts arguments in a function call's parentheses.
	 *
	 * Counts comma-separated arguments at the top nesting level,
	 * skipping nested parentheses, arrays, and braces.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the T_STRING token (function name).
	 *
	 * @return int The number of arguments (0 if empty parentheses).
	 */
	protected function countArguments( File $phpcsFile, int $stackPtr ): int {
		$tokens = $phpcsFile->getTokens();

		$open = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		if ( $open === false || $tokens[ $open ]['code'] !== T_OPEN_PARENTHESIS ) {
			return 0;
		}

		if ( ! isset( $tokens[ $open ]['parenthesis_closer'] ) ) {
			return 0;
		}

		$close = $tokens[ $open ]['parenthesis_closer'];

		// Check if parentheses are empty.
		$firstContent = $phpcsFile->findNext( T_WHITESPACE, $open + 1, $close, true );
		if ( $firstContent === false ) {
			return 0;
		}

		$count = 1;
		$depth = 0;

		for ( $i = $open + 1; $i < $close; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( in_array( $code, [ T_OPEN_PARENTHESIS, T_OPEN_SHORT_ARRAY, T_OPEN_CURLY_BRACKET ], true ) ) {
				++$depth;
			} elseif ( in_array( $code, [ T_CLOSE_PARENTHESIS, T_CLOSE_SHORT_ARRAY, T_CLOSE_CURLY_BRACKET ], true ) ) {
				--$depth;
			} elseif ( $code === T_COMMA && $depth === 0 ) {
				++$count;
			}
		}

		return $count;
	}
}
