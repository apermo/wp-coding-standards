<?php
/**
 * Flag deprecated FILTER_SANITIZE_STRING usage.
 *
 * @package Apermo\Sniffs\PHP
 */

namespace Apermo\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags usage of FILTER_SANITIZE_STRING (deprecated since PHP 8.1).
 *
 * The constant never actually sanitized input safely and gives a false
 * sense of security. Use sanitize_text_field() or a specific sanitizer.
 *
 * Error codes:
 * - Found: deprecated filter constant usage detected
 */
class NoFilterSanitizeStringSniff implements Sniff {

	/**
	 * Deprecated filter constants to flag.
	 *
	 * @var array<string, string>
	 */
	private const FORBIDDEN_CONSTANTS = [
		'FILTER_SANITIZE_STRING'       => 'sanitize_text_field()',
		'FILTER_SANITIZE_STRIPPED'     => 'sanitize_text_field()',
		'FILTER_SANITIZE_MAGIC_QUOTES' => 'wp_slash()',
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
		$tokens  = $phpcsFile->getTokens();
		$content = $tokens[ $stackPtr ]['content'];

		if ( ! isset( self::FORBIDDEN_CONSTANTS[ $content ] ) ) {
			return;
		}

		// Make sure this is used as a constant (not a string or function name).
		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		if ( $prev !== false ) {
			$prevCode = $tokens[ $prev ]['code'];
			// Skip if it's part of a function/method call or definition.
			if ( in_array( $prevCode, [ T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR, T_FUNCTION ], true ) ) {
				return;
			}
		}

		// Skip if followed by ( — it's being used as a function name.
		$next = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		if ( $next !== false && $tokens[ $next ]['code'] === T_OPEN_PARENTHESIS ) {
			return;
		}

		$phpcsFile->addError(
			'%s is deprecated since PHP 8.1 and never truly sanitized; use %s instead',
			$stackPtr,
			'Found',
			[ $content, self::FORBIDDEN_CONSTANTS[ $content ] ],
		);
	}
}
