<?php
/**
 * Unit test for the NoFilterSanitizeString sniff.
 *
 * @package Apermo\Tests\PHP
 */

namespace Apermo\Tests\PHP;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.PHP.NoFilterSanitizeString.
 */
class NoFilterSanitizeStringUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			7  => 1, // FILTER_SANITIZE_STRING
			10 => 1, // FILTER_SANITIZE_STRIPPED
			13 => 1, // FILTER_SANITIZE_MAGIC_QUOTES
		];
	}

	/**
	 * Returns the lines where warnings should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getWarningList( $testFile = '' ) {
		return [];
	}
}
