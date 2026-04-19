<?php
declare(strict_types=1);

/**
 * Tests the ExplainCommentedOutCode sniff.
 *
 * @package Apermo\Tests\PHP
 */

namespace Apermo\Tests\PHP;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Tests Apermo.PHP.ExplainCommentedOutCode.
 */
class ExplainCommentedOutCodeUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			30 => 1,
			36 => 1,
			39 => 1,
			43 => 1,
			47 => 1,
			50 => 1,
			59 => 1,
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
