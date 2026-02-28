<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1

// WARNING: hardcoded wp_posts.
$s = 'SELECT * FROM wp_posts';

// WARNING: hardcoded custom table.
$s = 'SELECT * FROM wp_my_custom_table';

// OK: no wp_ prefix.
$s = 'SELECT * FROM custom_table';
