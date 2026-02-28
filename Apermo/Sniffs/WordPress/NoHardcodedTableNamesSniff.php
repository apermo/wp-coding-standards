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
 * (e.g. wp_posts, wp_options) instead of $wpdb->prefix or
 * $wpdb->tablename properties.
 *
 * Hardcoded table names break when the prefix is customized.
 *
 * Error codes:
 * - Found: hardcoded wp_ table name in SQL string
 */
class NoHardcodedTableNamesSniff implements Sniff {

	/**
	 * Known WordPress core table base names (without prefix).
	 *
	 * @var array<string>
	 */
	private const TABLE_NAMES = [
		'posts',
		'postmeta',
		'comments',
		'commentmeta',
		'terms',
		'term_taxonomy',
		'term_relationships',
		'termmeta',
		'options',
		'links',
		'users',
		'usermeta',
	];

	/**
	 * Regex pattern matching hardcoded wp_ table names.
	 *
	 * @var string
	 */
	private string $pattern = '';

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		$names         = implode( '|', self::TABLE_NAMES );
		$this->pattern = '/\bwp_(' . $names . ')\b/i';

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

		if ( preg_match( $this->pattern, $content, $matches ) !== 1 ) {
			return;
		}

		$phpcsFile->addWarning(
			'Hardcoded table name "%s" detected; use $wpdb->%s or $wpdb->prefix instead',
			$stackPtr,
			'Found',
			[ 'wp_' . $matches[1], $matches[1] ],
		);
	}
}
