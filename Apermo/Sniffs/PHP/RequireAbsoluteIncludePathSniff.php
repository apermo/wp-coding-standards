<?php
declare(strict_types=1);

/**
 * Flags require/include with relative paths.
 *
 * @package Apermo\Sniffs\PHP
 */

namespace Apermo\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags require/include statements that use relative paths.
 * Absolute paths (using __DIR__, __FILE__, constants, variables,
 * or function calls) are always preferred for predictable resolution.
 *
 * Error codes:
 * - RelativePath: relative path detected in require/include
 */
class RequireAbsoluteIncludePathSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE ];
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

		// Find the first meaningful token after the keyword.
		// There is always at least a semicolon or string after a require/include.
		$first = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		// Handle parenthesized syntax: require('file.php').
		if ( $tokens[ $first ]['code'] === T_OPEN_PARENTHESIS ) {
			$first = $phpcsFile->findNext( T_WHITESPACE, $first + 1, null, true );
		}

		$code = $tokens[ $first ]['code'];

		// Absolute indicators: __DIR__, __FILE__, constants, variables, function calls.
		if ( in_array( $code, [ T_DIR, T_FILE, T_VARIABLE ], true ) ) {
			return;
		}

		// T_STRING could be a constant (ABSPATH) or function call — assume absolute.
		if ( $code === T_STRING ) {
			return;
		}

		// String literal: check if it starts with /.
		if ( $code === T_CONSTANT_ENCAPSED_STRING ) {
			$value = substr( $tokens[ $first ]['content'], 1, -1 );
			if ( strpos( $value, '/' ) === 0 ) {
				return;
			}

			$keyword = $tokens[ $stackPtr ]['content'];
			$phpcsFile->addError(
				'%s with relative path detected; use an absolute path (__DIR__ . \'/...\') instead',
				$stackPtr,
				'RelativePath',
				[ $keyword ],
			);
		}
	}
}
