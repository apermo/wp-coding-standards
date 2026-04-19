<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1
// phpcs:disable SlevomatCodingStandard.TypeHints
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// WARNING: unchecked wp_remote_get().
function bad( string $u ): string {
	$r = wp_remote_get( $u );
	return wp_remote_retrieve_body( $r );
}

// OK: checked with is_wp_error().
function good( string $u ): string {
	$r = wp_remote_get( $u );
	if ( is_wp_error( $r ) ) {
		return '';
	}
	return wp_remote_retrieve_body( $r );
}

// OK: wp_delete_post returns WP_Post|false|null, never WP_Error.
function delete_ok( int $id ): void {
	$r = wp_delete_post( $id, true );
}

// OK: wp_mail returns bool, never WP_Error.
function mail_ok( string $body ): void {
	$r = wp_mail( 'recipient@example.tld', 'subject', $body );
}

// OK: wp_upload_bits returns array with 'error' string key, never WP_Error.
function upload_ok( string $name, string $bits ): void {
	$r = wp_upload_bits( $name, null, $bits );
}
