<?php
/**
 * Unit test for the ImplicitPostFunction sniff.
 *
 * @package Apermo\Tests\WordPress
 */

namespace Apermo\Tests\WordPress;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.WordPress.ImplicitPostFunction.
 */
class ImplicitPostFunctionUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			11 => 1,
			12 => 1,
			13 => 1,
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
			16 => 1,
			17 => 1,
			18 => 1,
			21 => 1,
			22 => 1,
			38 => 1,
			44 => 1,
			49 => 1,
			53 => 1,
		];
	}
}
