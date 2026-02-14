<?php
// OK — flat with few keys.
$flat = [ 'a' => 1, 'b' => 2 ];

// OK — nested but numeric (list of lists).
$grid = [ [ 1, 2 ], [ 3, 4 ] ];

// OK — 2 associative levels (equals warnDepth=2, not exceeded).
$shallow = [ 'db' => [ 'host' => 'localhost' ] ];

// WARNING TooDeep — 3 associative levels.
$deep = [ 'a' => [ 'b' => [ 'c' => 1 ] ] ];

// ERROR TooDeepError — 5 associative levels.
$veryDeep = [ 'a' => [ 'b' => [ 'c' => [ 'd' => [ 'e' => 1 ] ] ] ] ];

// WARNING TooManyKeys — 6 keys.
$user = [
	'id'       => 1,
	'name'     => 'John',
	'email'    => 'john@example.com',
	'role'     => 'admin',
	'active'   => true,
	'verified' => true,
];

// ERROR TooManyKeysError — 11 keys.
$big = [
	'k1'  => 1,
	'k2'  => 2,
	'k3'  => 3,
	'k4'  => 4,
	'k5'  => 5,
	'k6'  => 6,
	'k7'  => 7,
	'k8'  => 8,
	'k9'  => 9,
	'k10' => 10,
	'k11' => 11,
];

// OK — numeric array with many elements.
$numbers = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11 ];

// OK — array() long syntax, few keys.
$longSyntax = array( 'a' => 1, 'b' => 2 );

// OK — empty array.
$empty = [];

// WARNING TooDeep — long syntax, 3 associative levels.
$deepLong = array( 'a' => array( 'b' => array( 'c' => 1 ) ) );

// WARNING TooDeep + WARNING TooManyKeys — both checks fire independently.
$both = [
	'a' => [ 'b' => [ 'c' => 1 ] ],
	'd' => 2,
	'e' => 3,
	'f' => 4,
	'g' => 5,
	'h' => 6,
];

// OK — non-associative wrapper around associative inner arrays.
$mixed = [ [ 'a' => 1 ], [ 'b' => 2 ] ];
