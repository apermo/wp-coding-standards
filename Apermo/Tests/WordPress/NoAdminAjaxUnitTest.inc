<?php
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// ERROR: wp_ajax_ hook (single quotes).
add_action( 'wp_ajax_my_action', 'my_callback' );

// ERROR: wp_ajax_nopriv_ hook.
add_action( 'wp_ajax_nopriv_my_action', 'my_callback' );

// ERROR: wp_ajax_ hook (double quotes).
add_action( "wp_ajax_my_action", "my_callback" );

// OK: non-ajax hook.
add_action( 'init', 'my_init_callback' );

// OK: non-ajax hook.
add_action( 'wp_enqueue_scripts', 'my_enqueue' );

// OK: dynamic hook name (variable, cannot analyze).
add_action( $hook_name, 'my_callback' );

// OK: concatenated hook name (cannot analyze).
add_action( 'wp_ajax_' . $action, 'my_callback' );

// OK: method call, not global function.
$obj->add_action( 'wp_ajax_my_action', 'my_callback' );

// OK: static method call.
Hooks::add_action( 'wp_ajax_my_action', 'my_callback' );
