<?php
declare(strict_types=1);

/**
 * Unit test for the RequireAbsoluteIncludePath sniff.
 *
 * @package Apermo\Tests\PHP
 */

namespace Apermo\Tests\PHP;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.PHP.RequireAbsoluteIncludePath.
 */
class RequireAbsoluteIncludePathUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			5  => 1, // require 'file.php'.
			8  => 1, // require_once 'lib/functions.php'.
			11 => 1, // include 'template.php'.
			14 => 1, // include_once 'helpers.php'.
			17 => 1, // require( 'file.php' ).
			20 => 1, // require './file.php'.
			23 => 1, // require '../file.php'.
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
