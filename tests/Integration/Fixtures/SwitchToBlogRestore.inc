<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1
// phpcs:disable SlevomatCodingStandard.TypeHints
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// ERROR: missing restore_current_blog().
function bad( int $id ): void {
	switch_to_blog( $id );
	$p = get_posts();
}

// OK: has restore_current_blog().
function good( int $id ): void {
	switch_to_blog( $id );
	$p = get_posts();
	restore_current_blog();
}
