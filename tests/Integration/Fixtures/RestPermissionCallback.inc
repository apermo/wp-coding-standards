<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// ERROR: missing permission_callback.
register_rest_route( 'ns/v1', '/test', [
	'methods'  => 'GET',
	'callback' => 'cb',
] );

// OK: has permission_callback.
register_rest_route( 'ns/v1', '/test', [
	'methods'             => 'GET',
	'callback'            => 'cb',
	'permission_callback' => '__return_true',
] );
