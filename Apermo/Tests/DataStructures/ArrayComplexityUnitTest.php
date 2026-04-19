<?php
declare(strict_types=1);

/**
 * Tests the ArrayComplexity sniff.
 *
 * @package Apermo\Tests\DataStructures
 */

namespace Apermo\Tests\DataStructures;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Tests Apermo.DataStructures.ArrayComplexity.
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
			35  => 1,
			53  => 1,
			128 => 1,
			140 => 1,
			167 => 1,
			176 => 1,
			202 => 1,
			220 => 1,
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
			32 => 1,
			38 => 1,
			87 => 1,
			90 => 2,
		];
	}
}
