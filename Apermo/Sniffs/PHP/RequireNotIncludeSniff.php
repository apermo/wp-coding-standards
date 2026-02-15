<?php
/**
 * Enforce require/require_once over include/include_once.
 *
 * @package Apermo\Sniffs\PHP
 */

namespace Apermo\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags usage of include and include_once, which silently continue on
 * failure. Use require/require_once instead for predictable failures.
 */
class RequireNotIncludeSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_INCLUDE, T_INCLUDE_ONCE ];
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
		$tokens = $phpcsFile->getTokens();

		if ( $tokens[ $stackPtr ]['code'] === T_INCLUDE_ONCE ) {
			$phpcsFile->addError(
				'Use require_once instead of include_once; include_once silently continues on failure',
				$stackPtr,
				'IncludeOnceFound'
			);
		} else {
			$phpcsFile->addError(
				'Use require instead of include; include silently continues on failure',
				$stackPtr,
				'IncludeFound'
			);
		}
	}
}
