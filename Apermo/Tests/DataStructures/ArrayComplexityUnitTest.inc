<?php
// phpcs:disable Apermo.Commenting.DocSummaryStyle -- Test markers (ERROR/OK) aren't real docblocks.
// OK — flat with few keys.
$flat = [ 'a' => 1, 'b' => 2 ];

// OK — nested but numeric (list of lists).
$grid = [ [ 1, 2 ], [ 3, 4 ] ];

// OK — 3 associative levels (equals warnDepth=3, not exceeded).
// Mirrors WP_Query's `meta_query` shape, which is canonical WP usage.
$wp_query_with_meta = [
	'post_type'  => 'post',
	'meta_query' => [
		'relation' => 'AND',
		[ 'key' => 'foo', 'compare' => 'EXISTS' ],
		[ 'key' => 'bar', 'compare' => 'EXISTS' ],
	],
];

// OK — typical WP_Query arg set (7 keys, below warnKeys=10).
$query_args = [
	'post_type'      => 'any',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'meta_key'       => 'x',
	'meta_value'     => 'y',
	'no_found_rows'  => true,
];

// WARNING TooDeep — 4 associative levels.
$deep4 = [ 'a' => [ 'b' => [ 'c' => [ 'd' => 1 ] ] ] ];

// ERROR TooDeepError — 6 associative levels.
$deep6 = [ 'a' => [ 'b' => [ 'c' => [ 'd' => [ 'e' => [ 'f' => 1 ] ] ] ] ] ];

// WARNING TooManyKeys — 11 keys.
$many11 = [
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

// ERROR TooManyKeysError — 21 keys.
$many21 = [
	'k01' => 1,
	'k02' => 2,
	'k03' => 3,
	'k04' => 4,
	'k05' => 5,
	'k06' => 6,
	'k07' => 7,
	'k08' => 8,
	'k09' => 9,
	'k10' => 10,
	'k11' => 11,
	'k12' => 12,
	'k13' => 13,
	'k14' => 14,
	'k15' => 15,
	'k16' => 16,
	'k17' => 17,
	'k18' => 18,
	'k19' => 19,
	'k20' => 20,
	'k21' => 21,
];

// OK — numeric array with many elements.
$numbers = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15 ];

// OK — array() long syntax, few keys.
$long_syntax = array( 'a' => 1, 'b' => 2 );

// OK — empty array.
$empty = [];

// WARNING TooDeep — long syntax, 4 associative levels.
$deep_long4 = array( 'a' => array( 'b' => array( 'c' => array( 'd' => 1 ) ) ) );

// WARNING TooDeep + WARNING TooManyKeys — both checks fire independently.
$both = [
	'a'   => [ 'b' => [ 'c' => [ 'd' => 1 ] ] ],
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

// OK — non-associative wrapper around associative inner arrays.
$mixed = [ [ 'a' => 1 ], [ 'b' => 2 ] ];

// OK — function call inside array.
$config = [ 'merged' => array_merge( [ 'a' => 1 ], [ 'b' => 2 ] ) ];

// OK — numeric array nested inside associative array.
$items = [ 'list' => [ 1, 2, 3 ] ];

// OK — variable-extraction pattern: complex sub-array is lifted to its own variable,
// keeping both literals within warnDepth=3.
$meta_query_body = [
	'relation' => 'AND',
	[ 'key' => 'foo', 'compare' => 'EXISTS' ],
	[ 'key' => 'bar', 'compare' => 'EXISTS' ],
];
$extracted_args = [
	'post_type'  => 'any',
	'meta_query' => $meta_query_body,
];

// --- Parameter-signature checks (ComplexParameter*) --- //

// ERROR ComplexParameterKeys — 6-key default value (> parameterMaxKeys=5).
function with_wide_default( $opts = [
	'k1' => 1,
	'k2' => 2,
	'k3' => 3,
	'k4' => 4,
	'k5' => 5,
	'k6' => 6,
] ) {
	return $opts;
}

// ERROR ComplexParameterDepth — 3-level default value (> parameterMaxDepth=2).
function with_deep_default( $opts = [ 'a' => [ 'b' => [ 'c' => 1 ] ] ] ) {
	return $opts;
}

// OK — simple default (5 keys, depth 1).
function with_simple_default( $opts = [ 'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5 ] ) {
	return $opts;
}

// OK — numeric default (no associative keys, depth still 0).
function with_numeric_default( $opts = [ 1, 2, 3, 4, 5, 6 ] ) {
	return $opts;
}

// OK — no default at all.
function without_default( $opts ) {
	return $opts;
}

// OK — scalar default.
function with_scalar_default( $opts = 'x' ) {
	return $opts;
}

/**
 * ERROR ComplexParameterKeys — docblock shape with 6 top-level entries.
 *
 * @param array{a: int, b: int, c: int, d: int, e: int, f: int} $opts Options.
 */
function with_wide_shape( array $opts ) {
	return $opts;
}

/**
 * ERROR ComplexParameterDepth — docblock shape depth 3.
 *
 * @param array{a: array{b: array{c: int}}} $opts Options.
 */
function with_deep_shape( array $opts ) {
	return $opts;
}

/**
 * OK — docblock shape within limits (2 keys, depth 1).
 *
 * @param array{a: int, b: int} $opts Options.
 */
function with_simple_shape( array $opts ) {
	return $opts;
}

/**
 * OK — @param without array{} shape (just array type) is ignored.
 *
 * @param array $opts Options.
 */
function with_untyped_array( array $opts ) {
	return $opts;
}

class ArrayComplexityMethodHost {
	// ERROR ComplexParameterKeys — method with a wide array default.
	public function method_with_wide_default( $opts = [
		'k1' => 1,
		'k2' => 2,
		'k3' => 3,
		'k4' => 4,
		'k5' => 5,
		'k6' => 6,
	] ) {
		return $opts;
	}
}

// OK — closure with simple default.
$simple_closure = function ( $opts = [ 'a' => 1 ] ) {
	return $opts;
};

// ERROR ComplexParameterDepth — closure with deep default.
$deep_closure = function ( $opts = [ 'a' => [ 'b' => [ 'c' => 1 ] ] ] ) {
	return $opts;
};
