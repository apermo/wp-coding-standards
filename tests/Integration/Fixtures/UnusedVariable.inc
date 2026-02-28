<?php
// phpcs:disable WordPress,Squiz,PSR1,Apermo,Generic,PSR2,PEAR,Universal
// phpcs:disable SlevomatCodingStandard.TypeHints,SlevomatCodingStandard.Namespaces,SlevomatCodingStandard.PHP
// phpcs:disable SlevomatCodingStandard.ControlStructures,SlevomatCodingStandard.Functions,SlevomatCodingStandard.Arrays
// phpcs:disable SlevomatCodingStandard.Complexity,SlevomatCodingStandard.Classes

// Unused variable should be flagged (line 8).
function unused_var() {
	$unused = 42;
	return 1;
}

// Used variable should be allowed (line 14).
function used_var() {
	$used = 42;
	return $used;
}

// Foreach with unused value but used key — should be allowed (line 20).
function foreach_keys() {
	$items = [ 'a' => 1, 'b' => 2 ];
	$keys = [];
	foreach ( $items as $key => $value ) {
		$keys[] = $key;
	}
	return $keys;
}
