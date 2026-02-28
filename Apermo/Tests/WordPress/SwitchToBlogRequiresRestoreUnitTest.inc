<?php
// phpcs:disable SlevomatCodingStandard.TypeHints
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// ERROR: switch_to_blog without restore.
function missing_restore( int $blog_id ): void {
	switch_to_blog( $blog_id );
	$posts = get_posts();
}

// OK: switch_to_blog with restore.
function has_restore( int $blog_id ): void {
	switch_to_blog( $blog_id );
	$posts = get_posts();
	restore_current_blog();
}

// OK: method call, not global function.
function method_call( int $blog_id, object $ms ): void {
	$ms->switch_to_blog( $blog_id );
}

// OK: static method call.
function static_call( int $blog_id ): void {
	MS::switch_to_blog( $blog_id );
}

// ERROR: top-level switch_to_blog without restore.
switch_to_blog( 2 );
