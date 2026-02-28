<?php
declare(strict_types=1);

/**
 * Flag hardcoded WordPress table names in SQL.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags SQL strings containing hardcoded WordPress table names
 * (e.g. wp_posts, wp_my_custom_table) instead of $wpdb->prefix
 * or $wpdb->tablename properties.
 *
 * Any hardcoded wp_ prefix breaks when the table prefix is customized.
 *
 * Error codes:
 * - Found: hardcoded wp_ table name in SQL string
 */
class NoHardcodedTableNamesSniff implements Sniff {

	/**
	 * Regex matching any hardcoded wp_ prefixed table name.
	 *
	 * @var string
	 */
	private const PATTERN = '/\bwp_(\w+)\b/i';

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

		if ( preg_match( self::PATTERN, $content, $matches ) !== 1 ) {
			return;
		}

		$phpcsFile->addWarning(
			'Hardcoded table name "%s" detected; use $wpdb->prefix or $wpdb->tablename instead',
			$stackPtr,
			'Found',
			[ 'wp_' . $matches[1] ],
		);
	}
}
