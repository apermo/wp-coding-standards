<?php
/**
 * Forbid nested closures and arrow functions.
 *
 * @package Apermo\Sniffs\Functions
 */

namespace Apermo\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags closures and arrow functions that are nested inside another
 * closure or arrow function. Extract the inner callback to a named
 * function instead.
 */
class ForbiddenNestedClosureSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register(): array {
		return [ T_CLOSURE, T_FN ];
	}

	/**
	 * Processes a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ): void {
		if ( $this->isNestedInClosureOrArrowFunction( $phpcsFile, $stackPtr ) ) {
			$phpcsFile->addWarning(
				'Nested closures and arrow functions are forbidden; extract to a named function instead',
				$stackPtr,
				'NestedClosure'
			);
		}
	}

	/**
	 * Checks whether the token is inside a closure or arrow function.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return bool
	 */
	private function isNestedInClosureOrArrowFunction( File $phpcsFile, int $stackPtr ): bool {
		if ( $phpcsFile->hasCondition( $stackPtr, [ T_CLOSURE ] ) ) {
			return true;
		}

		// T_FN (arrow functions) are not tracked in PHPCS's conditions
		// system because they lack curly braces. Walk backward to check
		// if this token falls inside an arrow function's scope.
		$tokens = $phpcsFile->getTokens();
		$prev   = $phpcsFile->findPrevious( T_FN, $stackPtr - 1 );

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
}
