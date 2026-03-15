<?php
declare(strict_types=1);

/**
 * Unit test for the GlobalFunctionQualification sniff.
 *
 * @package Apermo\Tests\Namespaces
 */

namespace Apermo\Tests\Namespaces;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.Namespaces.GlobalFunctionQualification.
 */
class GlobalFunctionQualificationUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		if ( $testFile === 'GlobalFunctionQualificationUnitTest.1.inc' ) {
			return [];
		}

		return [
			6  => 1, // strlen without backslash
			12 => 1, // \plugin_dir_path with backslash
			20 => 1, // in_array without backslash
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
