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
