<?php
/**
 * Flag global $post usage inside functions.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use PHP_CodeSniffer\Files\File;

/**
 * Flags `global $post;` inside function, method, closure, and arrow
 * function scopes. Top-level (template) usage is allowed because the
 * WordPress loop sets $post there.
 *
 * Functions should receive WP_Post or a post ID as a parameter instead
 * of reaching into global state.
 */
class GlobalPostAccessSniff extends AbstractPostContextSniff {

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_GLOBAL ];
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
		if ( ! $this->isInsideFunctionScope( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$tokens = $phpcsFile->getTokens();

		for ( $i = $stackPtr + 1; $i < $phpcsFile->numTokens; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_SEMICOLON ) {
				break;
			}

			if ( $tokens[ $i ]['code'] === T_VARIABLE && $tokens[ $i ]['content'] === '$post' ) {
				$phpcsFile->addError(
					'Do not use "global $post" inside functions; pass WP_Post or post ID as a parameter instead',
					$i,
					'Found'
				);
			}
		}
	}
}
