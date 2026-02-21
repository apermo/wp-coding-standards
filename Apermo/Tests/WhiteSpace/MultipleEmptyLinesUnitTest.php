<?php
/**
 * Unit test for the MultipleEmptyLines sniff.
 *
 * @package Apermo\Tests\WhiteSpace
 */

namespace Apermo\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.WhiteSpace.MultipleEmptyLines.
 */
class MultipleEmptyLinesUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		if ( $testFile !== 'MultipleEmptyLinesUnitTest.inc' ) {
			return [];
		}

		return [
			5  => 1,
			8  => 1,
			16 => 1,
			25 => 1,
			31 => 1,
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
