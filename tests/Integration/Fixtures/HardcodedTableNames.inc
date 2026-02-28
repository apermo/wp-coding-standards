<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1

// WARNING: hardcoded table name after FROM.
$s = 'SELECT * FROM wp_posts';

// WARNING: custom prefix is equally hardcoded.
$s = 'SELECT * FROM myapp_posts';

// OK: placeholder, not a hardcoded name.
$s = 'SELECT * FROM %i WHERE id = 1';
