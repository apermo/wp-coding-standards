<?php
/**
 * Unit test for the ArrayComplexity sniff.
 *
 * @package Apermo\Tests\DataStructures
 */

namespace Apermo\Tests\DataStructures;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.DataStructures.ArrayComplexity.
 */
class ArrayComplexityUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			15 => 1,
			28 => 1,
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
			12 => 1,
			18 => 1,
			52 => 1,
			55 => 2,
		];
	}
}
