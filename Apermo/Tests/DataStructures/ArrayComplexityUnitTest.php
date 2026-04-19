<?php
declare(strict_types=1);

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
			34  => 1,
			52  => 1,
			127 => 1,
			139 => 1,
			166 => 1,
			175 => 1,
			201 => 1,
			219 => 1,
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
			31 => 1,
			37 => 1,
			86 => 1,
			89 => 2,
		];
	}
}
