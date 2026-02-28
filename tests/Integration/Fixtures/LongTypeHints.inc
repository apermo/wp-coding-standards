<?php
// phpcs:disable WordPress,Squiz,PSR1,Apermo,Generic,PSR2,PEAR,Universal
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
// phpcs:disable SlevomatCodingStandard.Namespaces,SlevomatCodingStandard.PHP

/**
 * Long type hint 'integer' should be flagged (line 10).
 *
 * @param integer $x The value.
 * @return integer
 */
function long_hint( $x ) {
	return $x;
}

/**
 * Short type hint 'int' should be allowed (line 20).
 *
 * @param int $x The value.
 * @return int
 */
function short_hint( $x ) {
	return $x;
}
