<?php

// Top-level: OK — template context where global $post is expected.
global $post;
echo $post->post_title;

// Inside function: ERROR on line 9.
function bad_function() {
	global $post;
	echo $post->post_title;
}

// Inside method: ERROR on line 15.
class BadClass {
	public function bad_method() {
		global $post;
		echo $post->post_title;
	}
}

// Inside closure: ERROR on line 22.
$closure = function () {
	global $post;
	echo $post->post_title;
};

// Other globals inside function: OK — not $post.
function uses_wpdb() {
	global $wpdb;
	$wpdb->get_results( 'SELECT 1' );
}

// Mixed globals: ERROR on $post only (line 33), $wpdb is OK.
function mixed_globals() {
	global $wpdb, $post;
	echo $post->post_title;
}

// Good: receive WP_Post as parameter — OK.
function good_function( WP_Post $post ) {
	echo $post->post_title;
}
