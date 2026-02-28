<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard.TypeHints,Apermo,Generic,PSR2,PEAR
// phpcs:disable SlevomatCodingStandard.Namespaces,SlevomatCodingStandard.PHP,SlevomatCodingStandard.Variables
// phpcs:disable Universal.UseStatements,Universal.Operators,Universal.ControlStructures
$data = [ 'key' => 'value' ];
// isset() ternary should be flagged (line 7).
$value = isset( $data['key'] ) ? $data['key'] : 'default';
// Null coalesce should be allowed (line 9).
$value = $data['key'] ?? 'default';
