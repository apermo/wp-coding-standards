<?php
// phpcs:disable WordPress,Squiz,PSR1,Apermo,Generic,PSR2,PEAR,Universal
// phpcs:disable SlevomatCodingStandard.TypeHints,SlevomatCodingStandard.Namespaces,SlevomatCodingStandard.PHP
// phpcs:disable SlevomatCodingStandard.ControlStructures,SlevomatCodingStandard.Functions
// phpcs:disable SlevomatCodingStandard.Variables

// Implicit array creation should be flagged (line 8).
function implicit_array(): array {
	$items[] = 'a';
	return $items;
}

// Explicit array then append should be allowed (line 14).
function explicit_array(): array {
	$items = [];
	$items[] = 'a';
	return $items;
}
