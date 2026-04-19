<?php
declare(strict_types=1);

/**
 * Tests the PreferModernStringFunctions sniff.
 *
 * @package Apermo\Tests\PHP
 */

namespace Apermo\Tests\PHP;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Tests Apermo.PHP.PreferModernStringFunctions.
 */
class PreferModernStringFunctionsUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			8  => 1, // strpos === false.
			11 => 1, // strpos !== false.
			14 => 1, // strpos === 0.
			17 => 1, // strpos !== 0.
			20 => 1, // false === strpos.
			23 => 1, // 0 === strpos.
			26 => 1, // strstr === false.
			29 => 1, // false !== strstr.
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
			48 => 1, // Warning mode: strpos === false.
		];
	}
}
