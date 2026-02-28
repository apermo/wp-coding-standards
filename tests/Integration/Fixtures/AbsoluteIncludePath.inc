<?php
// phpcs:disable Apermo.PHP.RequireNotInclude

// ERROR: relative path.
require 'file.php';

// OK: absolute path with __DIR__.
require __DIR__ . '/file.php';

// OK: absolute path with constant.
require ABSPATH . 'wp-settings.php';
