<?php
/**
 * Unit test for the ExitUsage sniff.
 *
 * @package Apermo\Tests\PHP
 */

namespace Apermo\Tests\PHP;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.PHP.ExitUsage.
 */
class ExitUsageUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			2 => 1, // die;
			3 => 1, // die()
			4 => 1, // die( 'message' )
			5 => 1, // exit; (bare)
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
