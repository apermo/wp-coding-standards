<?php
declare(strict_types=1);

/**
 * Discourages (object) casts that produce stdClass instances.
 *
 * @package Apermo\Sniffs\PHP
 */

namespace Apermo\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags (object) casts, which implicitly create stdClass instances.
 * Use a typed class instead for better static analysis and IDE support.
 */
class ForbiddenObjectCastSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_OBJECT_CAST ];
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
		$phpcsFile->addWarning(
			'Avoid (object) cast; use a typed class instead of stdClass',
			$stackPtr,
			'Found'
		);
	}
}
