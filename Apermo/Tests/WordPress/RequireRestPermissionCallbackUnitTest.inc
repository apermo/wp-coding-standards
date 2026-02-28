<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// ERROR: no args parameter at all.
register_rest_route( 'myplugin/v1', '/items' );

// ERROR: args array without permission_callback.
register_rest_route( 'myplugin/v1', '/items', [
	'methods'  => 'GET',
	'callback' => 'my_callback',
] );

// OK: args array with permission_callback.
register_rest_route( 'myplugin/v1', '/items', [
	'methods'             => 'GET',
	'callback'            => 'my_callback',
	'permission_callback' => '__return_true',
] );

// OK: args passed as variable (cannot verify statically).
$args = get_route_args();
register_rest_route( 'myplugin/v1', '/items', $args );

// ERROR: array() syntax without permission_callback.
register_rest_route( 'myplugin/v1', '/items', array(
	'methods'  => 'GET',
	'callback' => 'my_callback',
) );

// OK: array() syntax with permission_callback.
register_rest_route( 'myplugin/v1', '/items', array(
	'methods'             => 'GET',
	'callback'            => 'my_callback',
	'permission_callback' => function () {
		return current_user_can( 'edit_posts' );
	},
) );

// OK: not a function call (method call).
$router->register_rest_route( 'ns', '/path', [] );

// OK: not a function call (static method).
Router::register_rest_route( 'ns', '/path', [] );

// ERROR: multiple route definitions array, none with permission_callback.
register_rest_route( 'myplugin/v1', '/items', [
	[
		'methods'  => 'GET',
		'callback' => 'get_items',
	],
	[
		'methods'  => 'POST',
		'callback' => 'create_item',
	],
] );

// OK: function call result as args.
register_rest_route( 'myplugin/v1', '/items', build_args() );
