<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// WARNING: %s in identifier position.
$s = $wpdb->prepare( 'SELECT * FROM %s WHERE id = %d', $wpdb->posts, $id );

// OK: %i used correctly.
$s = $wpdb->prepare( 'SELECT * FROM %i WHERE id = %d', $wpdb->posts, $id );
