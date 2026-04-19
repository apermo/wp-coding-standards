<?php
declare(strict_types=1);

/**
 * Tests the DisallowPreIncrementDecrement sniff.
 *
 * @package Apermo\Tests\Operators
 */

namespace Apermo\Tests\Operators;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Tests Apermo.Operators.DisallowPreIncrementDecrement.
 */
class DisallowPreIncrementDecrementUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the test file being tested.
	 *
	 * @return array<int, int>
	 */
	protected function getErrorList( $testFile = '' ) {
		return [
			7  => 1, // ++$a
			9  => 1, // --$a
			11 => 1, // ++$obj->prop
			13 => 1, // ++self::$count
			15 => 1, // ++$arr['key']
			23 => 1, // ++$obj->items['key']
			25 => 1, // ++parent::$count
			29 => 1, // ++ $a (with whitespace)
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
