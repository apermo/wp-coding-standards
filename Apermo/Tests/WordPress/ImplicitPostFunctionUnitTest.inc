<?php

// Top-level: OK — template context.
get_the_title();
get_post();
the_content();

function test_implicit() {
	// Error: MissingArgument — no arg provided.
	get_post();
	get_the_title();
	get_permalink();
	get_the_content( '', false );
	get_the_date();

	// Error: NullArgument — literal null passed.
	get_post( null );
	get_the_title( null );

	// Warning: IntegerArgument — literal int or ->ID.
	get_the_title( 123 );
	get_post( $post->ID );
	get_the_date( '', 42 );

	// Error: NoPostParameter — function has no $post param.
	get_the_ID();
	the_ID();
	the_content();
	the_excerpt();
	the_title();
	the_date();
	the_time();
	the_modified_date();
	the_modified_time();
	the_post_thumbnail();
	the_post_thumbnail_url();
	post_custom();
	permalink_anchor();

	// OK: variable/expression (type unknown, assume correct).
	get_post( $post );
	get_the_title( $post );
	get_the_content( '', false, $p );
	edit_post_link( 'Edit', '<p>', '</p>', $post );
	get_the_date( '', $post );

	// OK: method/static/nullsafe — not function calls.
	$obj->get_post();
	SomeClass::get_the_title();
	$obj?->get_permalink();

	// Error: FQ call without arg.
	\get_the_title();
	// OK: FQ call with arg.
	\get_the_title( $p );

	// OK: named argument.
	get_the_content( post: $post );
	get_the_date( post: $post );

	// Warning: named argument with integer.
	edit_post_link( post: 123 );
}

// Error: closure scope.
$closure = function () {
	get_the_title();
};

// Error: arrow function scope.
$arrow = fn() => get_the_title();
