<?php
declare(strict_types=1);

/**
 * Unit test for the NoHardcodedTableNames sniff.
 *
 * @package Apermo\Tests\WordPress
 */

namespace Apermo\Tests\WordPress;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.WordPress.NoHardcodedTableNames.
 */
class NoHardcodedTableNamesUnitTest extends AbstractSniffUnitTest {

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
			6  => 1, // FROM wp_posts.
			9  => 1, // FROM wp_options.
			12 => 1, // FROM wp_my_custom_table.
			15 => 1, // FROM myapp_posts (custom prefix).
			18 => 1, // JOIN wp_postmeta.
			21 => 1, // INTO custom_table.
			24 => 1, // UPDATE wp_users.
			47 => 1, // $wpdb->prefix interpolation (warnPrefix on).
		];
	}
}
