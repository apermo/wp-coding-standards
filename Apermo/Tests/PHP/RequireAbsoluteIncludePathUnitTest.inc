<?php
// phpcs:disable Apermo.PHP.RequireNotInclude

// ERROR: relative path with require.
require 'file.php';

// ERROR: relative path with require_once.
require_once 'lib/functions.php';

// ERROR: relative path with include.
include 'template.php';

// ERROR: relative path with include_once.
include_once 'helpers.php';

// ERROR: relative path with parenthesized syntax.
require( 'file.php' );

// ERROR: relative path starting with ./
require './file.php';

// ERROR: relative path starting with ../
require '../file.php';

// OK: absolute path with __DIR__.
require __DIR__ . '/file.php';

// OK: absolute path with __FILE__.
require dirname( __FILE__ ) . '/file.php';

// OK: absolute path with constant.
require ABSPATH . 'wp-settings.php';

// OK: absolute path with variable.
require $path;

// OK: absolute Unix path.
require '/var/www/file.php';
