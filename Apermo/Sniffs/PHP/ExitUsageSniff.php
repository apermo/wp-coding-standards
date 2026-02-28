<?php
/**
 * Enforce exit() over die and bare exit.
 *
 * @package Apermo\Sniffs\PHP
 */

namespace Apermo\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags die, die(), and bare exit (without parentheses).
 * Enforces exit() as the canonical form. Auto-fixable.
 */
class ExitUsageSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_EXIT ];
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
		$tokens  = $phpcsFile->getTokens();
		$content = $tokens[ $stackPtr ]['content'];
		$is_die  = strtolower( $content ) === 'die';

		$next = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		$has_parens = $next !== false && $tokens[ $next ]['code'] === T_OPEN_PARENTHESIS;

		if ( $is_die ) {
			$fix = $phpcsFile->addFixableError(
				'Use exit() instead of die',
				$stackPtr,
				'DieFound',
			);

			if ( $fix ) {
				$phpcsFile->fixer->beginChangeset();
				$phpcsFile->fixer->replaceToken( $stackPtr, 'exit' );

				if ( ! $has_parens ) {
					$phpcsFile->fixer->addContent( $stackPtr, '()' );
				}

				$phpcsFile->fixer->endChangeset();
			}

			return;
		}

		if ( ! $has_parens ) {
			$fix = $phpcsFile->addFixableError(
				'Use exit() instead of bare exit',
				$stackPtr,
				'BareExit',
			);

			if ( $fix ) {
				$phpcsFile->fixer->addContent( $stackPtr, '()' );
			}
		}
	}
}
