<?php
/**
 * Unit test for the RequireHookDocBlock sniff.
 *
 * @package Apermo\Tests\Hooks
 */

namespace Apermo\Tests\Hooks;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.Hooks.RequireHookDocBlock.
 */
class RequireHookDocBlockUnitTest extends AbstractSniffUnitTest {

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
			21 => 1,
			25 => 1,
			30 => 1,
			37 => 1,
			74 => 1,
			84 => 2,
		];
	}
}
