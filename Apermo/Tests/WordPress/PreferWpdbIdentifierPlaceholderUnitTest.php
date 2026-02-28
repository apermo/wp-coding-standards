<?php
declare(strict_types=1);

/**
 * Unit test for the PreferWpdbIdentifierPlaceholder sniff.
 *
 * @package Apermo\Tests\WordPress
 */

namespace Apermo\Tests\WordPress;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test for Apermo.WordPress.PreferWpdbIdentifierPlaceholder.
 */
class PreferWpdbIdentifierPlaceholderUnitTest extends AbstractSniffUnitTest {

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
			6  => 1, // FROM %s.
			9  => 1, // JOIN %s.
			12 => 1, // INTO %s.
		];
	}
}
