<?php
/**
 * Unit test for the ConsistentAssignmentAlignment sniff.
 *
 * @package Apermo\Tests\Formatting
 */

namespace Apermo\Tests\Formatting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.Formatting.ConsistentAssignmentAlignment.
 */
class ConsistentAssignmentAlignmentUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			55 => 1,
			56 => 1,
			57 => 1,
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
			17 => 1,
			36 => 1,
			52 => 1,
		];
	}
}
