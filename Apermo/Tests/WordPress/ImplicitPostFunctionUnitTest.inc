<?php

// Top-level: OK — template context.
get_the_title();
get_post();
the_content();

// Inside function scope.
function test_errors() {
	// Error: direct access functions.
	get_post();
	get_the_ID();
	the_ID();

	// Warning: optional post param not provided.
	get_the_title();
	get_permalink();
	has_post_thumbnail();

	// Warning: no post param exists.
	the_content();
	the_excerpt();

	// OK: post argument provided.
	get_post( $id );
	get_the_title( $post );

	// OK: enough arguments for higher param positions.
	get_the_content( '', false, $p );
	edit_post_link( 'Edit', '<p>', '</p>', $id );

	// OK: method call, not a function call.
	$obj->get_post();
	SomeClass::get_the_title();
	$obj?->get_permalink();

	// Warning: fully-qualified call without post arg.
	\get_the_title();

	// OK: fully-qualified call with post arg.
	\get_the_title( $p );

	// Warning: not enough args for get_the_content (post is param 3).
	get_the_content( '', false );
}

// Inside closure: WARNING.
$closure = function () {
	get_the_title();
};

// Inside arrow function: WARNING.
$arrow = fn() => get_the_title();
