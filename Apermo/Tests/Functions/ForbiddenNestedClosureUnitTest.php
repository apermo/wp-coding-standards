<?php
/**
 * Unit test for the ForbiddenNestedClosure sniff.
 *
 * @package Apermo\Tests\Functions
 */

namespace Apermo\Tests\Functions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.Functions.ForbiddenNestedClosure.
 */
class ForbiddenNestedClosureUnitTest extends AbstractSniffUnitTest {

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
			25 => 1,
			32 => 1,
			36 => 1,
			41 => 1,
		];
	}
}
