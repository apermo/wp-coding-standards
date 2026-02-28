<?php
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// WARNING: add_option without autoload.
add_option( 'my_option', 'value' );

// WARNING: update_option without autoload.
update_option( 'my_option', 'value' );

// OK: add_option with autoload parameter.
add_option( 'my_option', 'value', '', false );

// OK: update_option with autoload parameter.
update_option( 'my_option', 'value', false );

// OK: add_option with named autoload parameter.
add_option( 'my_option', 'value', autoload: false );

// OK: update_option with named autoload parameter.
update_option( 'my_option', 'value', autoload: true );

// OK: method call, not global function.
$store->add_option( 'key', 'val' );
$store->update_option( 'key', 'val' );

// WARNING: add_option with 3 args (deprecated param, no autoload).
add_option( 'my_option', 'value', '' );

// OK: function definition named add_option.
function add_option( $key, $val ) {
	return true;
}

// OK: nullsafe method call.
$store?->update_option( 'key', 'val' );
