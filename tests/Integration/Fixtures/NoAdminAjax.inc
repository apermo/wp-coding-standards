<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1

// ERROR: wp_ajax_ hook.
add_action( 'wp_ajax_my_action', 'my_callback' );

// OK: non-ajax hook.
add_action( 'init', 'my_init_callback' );
