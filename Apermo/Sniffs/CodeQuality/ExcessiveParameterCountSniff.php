<?php
declare(strict_types=1);

/**
 * Flags functions with too many parameters.
 *
 * @package Apermo\Sniffs\CodeQuality
 */

namespace Apermo\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags functions and methods with an excessive number of parameters.
 * Too many parameters indicate the function is doing too much and
 * should accept an options array or parameter object instead.
 *
 * Error codes:
 * - TooMany: function has more parameters than the configured maximum
 */
class ExcessiveParameterCountSniff implements Sniff {

	/**
	 * Maximum allowed number of parameters.
	 *
	 * @var int
	 */
	public $maxParameters = 6;

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int|string>
	 */
	public function register() {
		return [ T_FUNCTION, T_CLOSURE, T_FN ];
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
		$params = $phpcsFile->getMethodParameters( $stackPtr );
		$count  = count( $params );

		if ( $count <= $this->maxParameters ) {
			return;
		}

		$phpcsFile->addWarning(
			'Function has %d parameters; maximum recommended is %d. Consider an options array or parameter object.',
			$stackPtr,
			'TooMany',
			[ $count, $this->maxParameters ],
		);
	}
}
