<?php
declare(strict_types=1);

/**
 * Unit test for the SapiDependentFeatures sniff.
 *
 * @package Apermo\Tests\PHP
 */

namespace Apermo\Tests\PHP;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.PHP.SapiDependentFeatures.
 */
class SapiDependentFeaturesUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			6 => 1, // INPUT_REQUEST.
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
			9  => 1, // INPUT_SERVER.
			12 => 1, // INPUT_ENV.
			33 => 1, // \INPUT_SERVER with leading backslash.
		];
	}
}
