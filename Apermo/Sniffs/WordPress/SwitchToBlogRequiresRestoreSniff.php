<?php
declare(strict_types=1);

/**
 * Flag switch_to_blog() without restore_current_blog().
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use Apermo\Sniffs\Helpers\FunctionCallDetectorTrait;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags switch_to_blog() calls that lack a matching
 * restore_current_blog() in the same scope.
 *
 * Forgetting to restore after switching silently operates
 * on the wrong site for all subsequent code.
 *
 * Error codes:
 * - MissingRestore: switch_to_blog() without restore_current_blog()
 */
class SwitchToBlogRequiresRestoreSniff implements Sniff {

	use FunctionCallDetectorTrait;

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
		$tokens = $phpcsFile->getTokens();

		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'switch_to_blog' ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$scope = $this->findScopeEnd( $phpcsFile, $stackPtr );

		if ( $this->hasRestoreInScope( $phpcsFile, $stackPtr, $scope ) ) {
			return;
		}

		$phpcsFile->addError(
			'switch_to_blog() must be followed by restore_current_blog() in the same scope',
			$stackPtr,
			'MissingRestore',
		);
	}

	/**
	 * Find the end of the enclosing scope.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Current position.
	 *
	 * @return int End position of the scope.
	 */
	private function findScopeEnd( File $phpcsFile, int $stackPtr ): int {
		$tokens = $phpcsFile->getTokens();

		// Walk up to find the enclosing function/method/closure scope.
		foreach ( $tokens[ $stackPtr ]['conditions'] as $ptr => $code ) {
			if ( in_array( $code, [ T_FUNCTION, T_CLOSURE, T_FN ], true ) ) {
				return $tokens[ $ptr ]['scope_closer'];
			}
		}

		// Top-level: scan to end of file.
		return $phpcsFile->numTokens - 1;
	}

	/**
	 * Check if restore_current_blog() is called after $stackPtr within the scope.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Position of switch_to_blog().
	 * @param int  $scopeEnd  End of the enclosing scope.
	 *
	 * @return bool Whether restore_current_blog() was found.
	 */
	private function hasRestoreInScope( File $phpcsFile, int $stackPtr, int $scopeEnd ): bool {
		$tokens = $phpcsFile->getTokens();
		$pos    = $stackPtr + 1;

		while ( $pos < $scopeEnd ) {
			$next = $phpcsFile->findNext( T_STRING, $pos, $scopeEnd );
			if ( $next === false ) {
				break;
			}

			if ( strtolower( $tokens[ $next ]['content'] ) === 'restore_current_blog' ) {
				return true;
			}

			$pos = $next + 1;
		}

		return false;
	}
}
