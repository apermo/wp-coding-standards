<?php
// phpcs:disable SlevomatCodingStandard.TypeHints
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// WARNING: hardcoded wp_posts in single-quoted SQL.
$sql = 'SELECT * FROM wp_posts WHERE post_status = "publish"';

// WARNING: hardcoded wp_options in double-quoted SQL.
$sql = "SELECT * FROM wp_options WHERE option_name = 'foo'";

// WARNING: hardcoded wp_postmeta.
$sql = 'SELECT * FROM wp_postmeta WHERE meta_key = "test"';

// WARNING: hardcoded wp_users.
$sql = 'SELECT * FROM wp_users';

// OK: using $wpdb->posts property.
$sql = "SELECT * FROM {$wpdb->posts} WHERE post_status = 'publish'";

// OK: using $wpdb->prefix.
$sql = "SELECT * FROM {$wpdb->prefix}custom_table";

// OK: not a table name (wp_ in a non-table context).
$text = 'The wp_cron system handles scheduling.';
