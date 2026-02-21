<?php
/**
 * Unit test for the ConsistentDoubleArrowAlignment sniff.
 *
 * @package Apermo\Tests\Arrays
 */

namespace Apermo\Tests\Arrays;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.Arrays.ConsistentDoubleArrowAlignment.
 */
class ConsistentDoubleArrowAlignmentUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			66 => 1,
			67 => 1,
			68 => 1,
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
		return [
			25 => 1,
			33 => 1,
		];
	}
}
