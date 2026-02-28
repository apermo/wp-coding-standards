<?php
// phpcs:disable WordPress,Squiz,PSR1,Apermo,Generic,PSR2,PEAR,Universal
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
// phpcs:disable SlevomatCodingStandard.Namespaces,SlevomatCodingStandard.PHP
// phpcs:disable SlevomatCodingStandard.TypeHints.LongTypeHints

/**
 * Null first should be flagged (line 11).
 *
 * @param null|string $x The value.
 */
function null_first( $x ): void {}

/**
 * Null last should be allowed (line 18).
 *
 * @param string|null $x The value.
 */
function null_last( $x ): void {}
