<?php
declare(strict_types=1);

/**
 * Tests the ExcessiveParameterCount sniff.
 *
 * @package Apermo\Tests\CodeQuality
 */

namespace Apermo\Tests\CodeQuality;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Tests Apermo.CodeQuality.ExcessiveParameterCount.
 */
class ExcessiveParameterCountUnitTest extends AbstractSniffUnitTest {

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
			6  => 1, // 7 params, default max 6.
			26 => 1, // closure with 7 params.
			31 => 1, // arrow function with 7 params.
			36 => 1, // 4 params, custom max 3.
		];
	}
}
