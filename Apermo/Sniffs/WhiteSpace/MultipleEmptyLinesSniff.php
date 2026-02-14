<?php
/**
 * Disallow multiple consecutive empty lines outside functions.
 *
 * @package Apermo\Sniffs\WhiteSpace
 */

namespace Apermo\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures no more than one consecutive empty line appears outside
 * functions and closures, where Squiz.WhiteSpace.SuperfluousWhitespace
 * already handles this check.
 */
class MultipleEmptyLinesSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_WHITESPACE ];
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
		// Need at least two previous tokens for the detection pattern.
		if ( $stackPtr < 2 ) {
			return;
		}

		$tokens = $phpcsFile->getTokens();

		// Skip inside functions/closures — Squiz handles those.
		if ( $phpcsFile->hasCondition( $stackPtr, [ T_FUNCTION, T_CLOSURE ] ) ) {
			return;
		}

		// Detect the start of a blank-line region using the same pattern
		// as Squiz.WhiteSpace.SuperfluousWhitespace:
		// - Previous token is on a prior line (current token starts a new line).
		// - Two-back token is on same line as one-back (previous line has content).
		if ( $tokens[ ( $stackPtr - 1 ) ]['line'] >= $tokens[ $stackPtr ]['line']
			|| $tokens[ ( $stackPtr - 2 ) ]['line'] !== $tokens[ ( $stackPtr - 1 ) ]['line']
		) {
			return;
		}

		// Find next non-whitespace token.
		$next = $phpcsFile->findNext( T_WHITESPACE, $stackPtr, null, true );
		if ( $next === false ) {
			return;
		}

		$lines = ( $tokens[ $next ]['line'] - $tokens[ $stackPtr ]['line'] );
		if ( $lines <= 1 ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'No more than 1 empty line allowed; found %s consecutive empty lines',
			$stackPtr,
			'MultipleEmptyLines',
			[ $lines ]
		);

		if ( $fix === true ) {
			$phpcsFile->fixer->beginChangeset();
			$i = $stackPtr;
			while ( $tokens[ $i ]['line'] !== $tokens[ $next ]['line'] ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
				$i++;
			}

			$phpcsFile->fixer->addNewlineBefore( $i );
			$phpcsFile->fixer->endChangeset();
		}
	}
}
