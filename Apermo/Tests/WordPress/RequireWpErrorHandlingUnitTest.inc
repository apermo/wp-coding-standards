<?php
// phpcs:disable SlevomatCodingStandard.TypeHints
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// WARNING: wp_remote_get() without is_wp_error() check.
function unchecked( string $url ): string {
	$response = wp_remote_get( $url );
	return wp_remote_retrieve_body( $response );
}

// OK: wp_remote_get() with is_wp_error() check.
function checked( string $url ): string {
	$response = wp_remote_get( $url );
	if ( is_wp_error( $response ) ) {
		return '';
	}
	return wp_remote_retrieve_body( $response );
}

// OK: not assigned to variable.
function fire_and_forget( string $url ): void {
	wp_remote_get( $url );
}

// WARNING: wp_insert_post() without check.
function insert_unchecked( array $args ): int {
	$post_id = wp_insert_post( $args );
	return $post_id;
}

// OK: wp_insert_post() with check.
function insert_checked( array $args ): int {
	$post_id = wp_insert_post( $args );
	if ( is_wp_error( $post_id ) ) {
		return 0;
	}
	return $post_id;
}

// OK: method call, not global function.
function method_call( string $url, object $client ): void {
	$response = $client->wp_remote_get( $url );
}

// OK: static method call.
function static_call( string $url ): void {
	$response = HTTP::wp_remote_get( $url );
}

// OK: assigned to array key (non-variable target, can't track).
function array_assign( string $url ): array {
	$results['response'] = wp_remote_get( $url );
	return $results;
}

// WARNING: top-level code without is_wp_error() check.
$response = wp_remote_get( 'https://example.com' );

// OK: wp_delete_post returns WP_Post|false|null, never WP_Error.
$deleted = wp_delete_post( 123, true );

// OK: wp_mail returns bool, never WP_Error.
$sent = wp_mail( 'recipient@example.tld', 'subject', 'body' );

// OK: wp_upload_bits returns array with 'error' string key, never WP_Error.
$upload = wp_upload_bits( 'name.txt', null, 'bits' );
