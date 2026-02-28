<?php
// phpcs:disable SlevomatCodingStandard.TypeHints
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// ERROR: INPUT_REQUEST is not supported.
$val = filter_input( INPUT_REQUEST, 'key' );

// WARNING: INPUT_SERVER is SAPI-dependent.
$val = filter_input( INPUT_SERVER, 'REQUEST_URI' );

// WARNING: INPUT_ENV is SAPI-dependent.
$val = filter_input( INPUT_ENV, 'HOME' );

// OK: INPUT_GET is safe.
$val = filter_input( INPUT_GET, 'page' );

// OK: INPUT_POST is safe.
$val = filter_input( INPUT_POST, 'action' );

// OK: INPUT_COOKIE is safe.
$val = filter_input( INPUT_COOKIE, 'session' );

// OK: method call, not global function.
$obj->filter_input( INPUT_SERVER, 'key' );

// OK: static method call.
Filters::filter_input( INPUT_SERVER, 'key' );

// OK: filter_input with no arguments (edge case).
$val = filter_input();
