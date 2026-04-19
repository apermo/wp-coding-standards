<?php
declare(strict_types=1);

/**
 * Tests the RequireRestPermissionCallback sniff.
 *
 * @package Apermo\Tests\WordPress
 */

namespace Apermo\Tests\WordPress;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Tests Apermo.WordPress.RequireRestPermissionCallback.
 */
class RequireRestPermissionCallbackUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			8  => 1, // No args at all.
			11 => 1, // Short array without permission_callback.
			28 => 1, // array() without permission_callback.
			49 => 1, // Nested route arrays, none with permission_callback.
			61 => 1, // Mixed nested routes, one missing permission_callback.
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
