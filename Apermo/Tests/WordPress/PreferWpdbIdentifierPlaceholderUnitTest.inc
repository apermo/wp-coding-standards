<?php
// phpcs:disable SlevomatCodingStandard.TypeHints
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// WARNING: %s after FROM in prepare().
$sql = $wpdb->prepare( 'SELECT * FROM %s WHERE id = %d', $wpdb->posts, $id );

// WARNING: %s after JOIN in prepare().
$sql = $wpdb->prepare( 'SELECT * FROM %i p JOIN %s pm ON p.ID = pm.post_id', $wpdb->posts, $wpdb->postmeta );

// WARNING: %s after INTO in prepare().
$sql = $wpdb->prepare( 'INSERT INTO %s (col) VALUES (%s)', $table, $val );

// OK: %i used correctly.
$sql = $wpdb->prepare( 'SELECT * FROM %i WHERE id = %d', $wpdb->posts, $id );

// OK: %s for value, not identifier.
$sql = $wpdb->prepare( 'SELECT * FROM %i WHERE name = %s', $wpdb->posts, $name );

// OK: not inside $wpdb->prepare().
$sql = sprintf( 'SELECT * FROM %s WHERE id = %d', 'posts', $id );

// OK: no identifier position.
$sql = $wpdb->prepare( 'SELECT %s FROM %i', $col, $wpdb->posts );

// OK: pattern match but not inside parentheses.
$sql = 'SELECT * FROM %s WHERE id = 1';

// OK: inside parentheses but not a function call (variable call).
$sql = $callback( 'SELECT * FROM %s' );

// OK: function called 'prepare' but no -> before it.
$sql = prepare( 'SELECT * FROM %s', $table );

// WARNING: numbered placeholder %1$s in identifier position.
$sql = $wpdb->prepare( 'SELECT * FROM %1$s WHERE id = %2$d', $wpdb->posts, $id );

// OK: not $wpdb (other object with prepare method).
$sql = $other->prepare( 'SELECT * FROM %s WHERE id = %d', $table, $id );
