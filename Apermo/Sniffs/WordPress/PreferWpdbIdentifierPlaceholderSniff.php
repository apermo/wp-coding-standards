<?php
declare(strict_types=1);

/**
 * Flag %s placeholder for identifiers in $wpdb->prepare().
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags $wpdb->prepare() calls where %s is used in identifier
 * positions (after FROM, JOIN, INTO, UPDATE, TABLE keywords).
 * The %i placeholder (WP 6.2+) properly escapes identifiers.
 *
 * Error codes:
 * - Found: %s in identifier position, use %i instead
 */
class PreferWpdbIdentifierPlaceholderSniff implements Sniff {

	/**
	 * SQL keywords after which a placeholder refers to an identifier.
	 *
	 * @var string
	 */
	private const IDENTIFIER_PATTERN = '/\b(?:FROM|JOIN|INTO|UPDATE|TABLE)\s+%(?:\d+\$)?s\b/i';

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int|string>
	 */
	public function register() {
		return [ T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING ];
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

		if ( preg_match( self::IDENTIFIER_PATTERN, $content ) !== 1 ) {
			return;
		}

		// Verify this string is inside a $wpdb->prepare() call context.
		if ( ! $this->isInWpdbPrepare( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$phpcsFile->addWarning(
			'Use %%i placeholder for SQL identifiers instead of %%s (WP 6.2+)',
			$stackPtr,
			'Found',
		);
	}

	/**
	 * Check if the token is part of a $wpdb->prepare() call.
	 *
	 * Walks back to find $wpdb->prepare pattern before the current
	 * string token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Position of the string token.
	 *
	 * @return bool Whether this is inside $wpdb->prepare().
	 */
	private function isInWpdbPrepare( File $phpcsFile, int $stackPtr ): bool {
		$tokens = $phpcsFile->getTokens();

		// Find the opening parenthesis that contains this token.
		if ( ! isset( $tokens[ $stackPtr ]['nested_parenthesis'] ) ) {
			return false;
		}

		$openers = $tokens[ $stackPtr ]['nested_parenthesis'];
		// Check the innermost parenthesis.
		$opener = array_key_last( $openers );

		// The token before the opening paren should be 'prepare'.
		$funcName = $phpcsFile->findPrevious( T_WHITESPACE, $opener - 1, null, true );
		if ( $funcName === false || $tokens[ $funcName ]['code'] !== T_STRING ) {
			return false;
		}

		if ( strtolower( $tokens[ $funcName ]['content'] ) !== 'prepare' ) {
			return false;
		}

		// The token before 'prepare' should be '->'.
		$arrow = $phpcsFile->findPrevious( T_WHITESPACE, $funcName - 1, null, true );
		if ( $arrow === false || $tokens[ $arrow ]['code'] !== T_OBJECT_OPERATOR ) {
			return false;
		}

		// The token before '->' should be $wpdb.
		$object = $phpcsFile->findPrevious( T_WHITESPACE, $arrow - 1, null, true );
		if ( $object === false || $tokens[ $object ]['content'] !== '$wpdb' ) {
			return false;
		}

		return true;
	}
}
