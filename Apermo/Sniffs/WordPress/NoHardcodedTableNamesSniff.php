<?php
declare(strict_types=1);

/**
 * Flag hardcoded table names in SQL strings.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags SQL strings containing hardcoded table names after SQL
 * keywords (FROM, JOIN, INTO, UPDATE, TABLE) instead of using
 * $wpdb->tablename or the %i placeholder.
 *
 * Optionally also flags $wpdb->prefix concatenation when
 * $warnPrefix is enabled (use $wpdb->tablename or %i instead).
 *
 * Error codes:
 * - Found:        hardcoded table name in SQL string
 * - PrefixConcat: $wpdb->prefix concatenation detected
 */
class NoHardcodedTableNamesSniff implements Sniff {

	/**
	 * SQL keywords after which a bare identifier is a table name.
	 * Matches the keyword followed by a hardcoded identifier that is
	 * not a placeholder (%s, %i, %1$s) or an interpolation ({$...}).
	 *
	 * @var string
	 */
	private const PATTERN = '/\b(?:FROM|JOIN|INTO|UPDATE|TABLE)\s+(?!%[sid]|%\d+\$[sid])([a-zA-Z_]\w*)\b/i';

	/**
	 * Matches $wpdb->prefix interpolation followed by a table name
	 * in double-quoted strings: "{$wpdb->prefix}tablename".
	 *
	 * @var string
	 */
	private const PREFIX_PATTERN = '/\{\$wpdb->prefix\}(\w+)/';

	/**
	 * Whether to flag $wpdb->prefix concatenation.
	 *
	 * When true, using $wpdb->prefix . 'table' or
	 * "{$wpdb->prefix}table" is flagged. Use $wpdb->tablename
	 * or the %i placeholder instead.
	 *
	 * @var bool
	 */
	public $warnPrefix = false;

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

		// Check for hardcoded table names after SQL keywords.
		if ( preg_match( self::PATTERN, $content, $matches ) === 1 ) {
			$phpcsFile->addWarning(
				'Hardcoded table name "%s" detected; use $wpdb->tablename or %%i placeholder instead',
				$stackPtr,
				'Found',
				[ $matches[1] ],
			);
			return;
		}

		// Check for $wpdb->prefix interpolation in double-quoted strings.
		if ( $this->warnPrefix && preg_match( self::PREFIX_PATTERN, $content, $matches ) === 1 ) {
			$phpcsFile->addWarning(
				'$wpdb->prefix concatenation detected for table "%s"; use $wpdb->tablename or %%i placeholder instead',
				$stackPtr,
				'PrefixConcat',
				[ $matches[1] ],
			);
		}
	}
}
