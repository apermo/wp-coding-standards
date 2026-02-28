<?php
/**
 * Enforce a minimum variable name length.
 *
 * @package Apermo\Sniffs\NamingConventions
 */

namespace Apermo\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags variables with names shorter than a configurable minimum.
 * Common short names (loop vars, ids) are allowed via a configurable allowlist.
 */
class MinimumVariableNameLengthSniff implements Sniff {

	/**
	 * Minimum variable name length (excluding $).
	 *
	 * @var int
	 */
	public int $minLength = 4;

	/**
	 * Allowed short variable names.
	 *
	 * Supports extend="true" in phpcs.xml to append instead of replace.
	 *
	 * @var array<string>
	 */
	public array $allowedShortNames = [
		'i',
		'id',
		'key',
		'url',
		'row',
		'tag',
		'map',
		'max',
		'min',
		'sql',
		'raw',
	];

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_VARIABLE ];
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
		$name   = ltrim( $tokens[ $stackPtr ]['content'], '$' );

		if ( $name === 'this' ) {
			return;
		}

		if ( strlen( $name ) >= $this->minLength ) {
			return;
		}

		if ( in_array( $name, $this->allowedShortNames, true ) ) {
			return;
		}

		$phpcsFile->addWarning(
			'Variable name "$%s" is only %d character(s) long; minimum is %d (or add to allowedShortNames)',
			$stackPtr,
			'TooShort',
			[ $name, strlen( $name ), $this->minLength ],
		);
	}
}
