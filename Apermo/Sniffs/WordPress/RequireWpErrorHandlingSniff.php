<?php
declare(strict_types=1);

/**
 * Flag unchecked WP_Error-returning function calls.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use Apermo\Sniffs\Helpers\FunctionCallDetectorTrait;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags calls to WordPress functions that may return WP_Error
 * when the return value is assigned to a variable but
 * is_wp_error() is not called on that variable in the same scope.
 *
 * Error codes:
 * - Unchecked: return value not checked with is_wp_error()
 */
class RequireWpErrorHandlingSniff implements Sniff {

	use FunctionCallDetectorTrait;

	/**
	 * WordPress functions that can return WP_Error.
	 *
	 * @var array<string, true>
	 */
	private const WP_ERROR_FUNCTIONS = [
		'wp_remote_get'      => true,
		'wp_remote_post'     => true,
		'wp_remote_head'     => true,
		'wp_remote_request'  => true,
		'wp_safe_remote_get'  => true,
		'wp_safe_remote_post' => true,
		'wp_safe_remote_head' => true,
		'wp_insert_post'     => true,
		'wp_update_post'     => true,
		'wp_delete_post'     => true,
		'wp_insert_term'     => true,
		'wp_update_term'     => true,
		'wp_insert_user'     => true,
		'wp_update_user'     => true,
		'wp_upload_bits'     => true,
		'wp_crop_image'      => true,
		'media_handle_upload' => true,
		'wp_mail'            => true,
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
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens   = $phpcsFile->getTokens();
		$funcName = strtolower( $tokens[ $stackPtr ]['content'] );

		if ( ! isset( self::WP_ERROR_FUNCTIONS[ $funcName ] ) ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$varName = $this->getAssignedVariable( $phpcsFile, $stackPtr );
		if ( $varName === null ) {
			// Not assigned to a variable — can't track.
			return;
		}

		$scopeEnd = $this->findScopeEnd( $phpcsFile, $stackPtr );

		if ( $this->hasIsWpErrorCheck( $phpcsFile, $stackPtr, $scopeEnd, $varName ) ) {
			return;
		}

		$phpcsFile->addWarning(
			'%s() can return WP_Error; check with is_wp_error() before using the result',
			$stackPtr,
			'Unchecked',
			[ $funcName ],
		);
	}

	/**
	 * Get the variable name the function result is assigned to.
	 *
	 * Looks for pattern: $var = func_name(
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Position of the function name.
	 *
	 * @return string|null The variable name or null.
	 */
	private function getAssignedVariable( File $phpcsFile, int $stackPtr ): ?string {
		$tokens = $phpcsFile->getTokens();

		// Look back past whitespace for '='.
		$equals = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		if ( $equals === false || $tokens[ $equals ]['code'] !== T_EQUAL ) {
			return null;
		}

		// Look back past whitespace for the variable.
		$var = $phpcsFile->findPrevious( T_WHITESPACE, $equals - 1, null, true );
		if ( $var === false || $tokens[ $var ]['code'] !== T_VARIABLE ) {
			return null;
		}

		return $tokens[ $var ]['content'];
	}

	/**
	 * Find the end of the enclosing scope.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Current position.
	 *
	 * @return int End position.
	 */
	private function findScopeEnd( File $phpcsFile, int $stackPtr ): int {
		$tokens = $phpcsFile->getTokens();

		foreach ( $tokens[ $stackPtr ]['conditions'] as $ptr => $code ) {
			if ( in_array( $code, [ T_FUNCTION, T_CLOSURE, T_FN ], true ) ) {
				return $tokens[ $ptr ]['scope_closer'];
			}
		}

		return $phpcsFile->numTokens - 1;
	}

	/**
	 * Check if is_wp_error($var) is called in the scope.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  Position after which to search.
	 * @param int    $scopeEnd  End of scope.
	 * @param string $varName   Variable name to look for.
	 *
	 * @return bool Whether is_wp_error() check was found.
	 */
	private function hasIsWpErrorCheck( File $phpcsFile, int $stackPtr, int $scopeEnd, string $varName ): bool {
		$tokens = $phpcsFile->getTokens();
		$pos    = $stackPtr + 1;

		while ( $pos < $scopeEnd ) {
			$next = $phpcsFile->findNext( T_STRING, $pos, $scopeEnd );
			if ( $next === false ) {
				break;
			}

			if ( strtolower( $tokens[ $next ]['content'] ) === 'is_wp_error' ) {
				// Check if the variable is passed as argument.
				$open = $phpcsFile->findNext( T_WHITESPACE, $next + 1, null, true );
				if ( $open !== false && $tokens[ $open ]['code'] === T_OPEN_PARENTHESIS ) {
					$arg = $phpcsFile->findNext( T_WHITESPACE, $open + 1, null, true );
					if ( $arg !== false && $tokens[ $arg ]['content'] === $varName ) {
						return true;
					}
				}
			}

			$pos = $next + 1;
		}

		return false;
	}
}
