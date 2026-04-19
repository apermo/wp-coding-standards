<?php
declare(strict_types=1);

/**
 * Tests the RequireHookDocBlock sniff.
 *
 * @package Apermo\Tests\Hooks
 */

namespace Apermo\Tests\Hooks;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Tests Apermo.Hooks.RequireHookDocBlock.
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
		return [
			22 => 1,
			26 => 1,
			31 => 1,
			38 => 1,
			75 => 1,
			85 => 2,
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
