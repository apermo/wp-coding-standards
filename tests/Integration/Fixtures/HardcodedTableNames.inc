<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1

// WARNING: hardcoded wp_posts.
$s = 'SELECT * FROM wp_posts';

// OK: no hardcoded table name.
$s = 'SELECT * FROM custom_table';
