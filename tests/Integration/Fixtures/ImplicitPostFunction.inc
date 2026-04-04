<?php
// phpcs:disable WordPress,Squiz,PSR1,Apermo.WordPress.GlobalPostAccess,Generic,PSR2,PEAR,Universal
// phpcs:disable SlevomatCodingStandard.TypeHints,SlevomatCodingStandard.Namespaces,SlevomatCodingStandard.PHP
// phpcs:disable SlevomatCodingStandard.ControlStructures,SlevomatCodingStandard.Functions
// phpcs:disable SlevomatCodingStandard.Variables,SlevomatCodingStandard.Classes

// get_post_format without argument should be flagged (line 9).
function test_get_post_format_missing() {
	$format = get_post_format();
}

// get_post_format with explicit post should be allowed (line 14).
function test_get_post_format_ok( $post ) {
	$format = get_post_format( $post );
}

// the_post_thumbnail without argument should be flagged (line 19).
function test_the_post_thumbnail_missing() {
	the_post_thumbnail();
}

// the_post_thumbnail with size arg should still be flagged (line 24).
function test_the_post_thumbnail_with_size() {
	the_post_thumbnail( 'large' );
}
