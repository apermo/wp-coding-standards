<?php
declare(strict_types=1);

/**
 * Unit test for the RequireWpErrorHandling sniff.
 *
 * @package Apermo\Tests\WordPress
 */

namespace Apermo\Tests\WordPress;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.WordPress.RequireWpErrorHandling.
 */
class RequireWpErrorHandlingUnitTest extends AbstractSniffUnitTest {

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
			7  => 1, // wp_remote_get without check.
			27 => 1, // wp_insert_post without check.
			57 => 1, // top-level wp_remote_get without check.
		];
	}
}
