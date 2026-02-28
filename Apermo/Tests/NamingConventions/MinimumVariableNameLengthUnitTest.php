<?php
/**
 * Unit test for the MinimumVariableNameLength sniff.
 *
 * @package Apermo\Tests\NamingConventions
 */

namespace Apermo\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.NamingConventions.MinimumVariableNameLength.
 */
class MinimumVariableNameLengthUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [];
	}

	/**
	 * Returns the lines where warnings should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getWarningList( $testFile = '' ) {
		return [
			2 => 1, // $a
			3 => 1, // $ab
			4 => 1, // $abc
		];
	}
}
