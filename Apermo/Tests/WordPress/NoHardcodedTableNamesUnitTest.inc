<?php
// phpcs:disable SlevomatCodingStandard.TypeHints
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// WARNING: hardcoded wp_posts after FROM.
$sql = 'SELECT * FROM wp_posts WHERE post_status = "publish"';

// WARNING: hardcoded wp_options in double-quoted SQL.
$sql = "SELECT * FROM wp_options WHERE option_name = 'foo'";

// WARNING: hardcoded custom table with wp_ prefix.
$sql = 'SELECT * FROM wp_my_custom_table WHERE id = 1';

// WARNING: hardcoded table with custom prefix.
$sql = 'SELECT * FROM myapp_posts WHERE id = 1';

// WARNING: hardcoded JOIN target.
$sql = 'SELECT * FROM %i JOIN wp_postmeta ON p.ID = pm.post_id';

// WARNING: hardcoded INSERT INTO target.
$sql = 'INSERT INTO custom_table (col) VALUES (1)';

// WARNING: hardcoded UPDATE target.
$sql = 'UPDATE wp_users SET name = "foo"';

// OK: using placeholder %s.
$sql = 'SELECT * FROM %s WHERE id = 1';

// OK: using identifier placeholder %i.
$sql = 'SELECT * FROM %i WHERE id = 1';

// OK: using numbered placeholder %1$s.
$sql = 'SELECT * FROM %1$s WHERE id = 1';

// OK: using $wpdb->posts property.
$sql = "SELECT * FROM {$wpdb->posts} WHERE post_status = 'publish'";

// OK: using $wpdb->prefix (warnPrefix disabled by default).
$sql = "SELECT * FROM {$wpdb->prefix}custom_table WHERE id = 1";

// OK: no SQL keyword before the identifier.
$text = 'Use wp_posts for blog content.';

// phpcs:set Apermo.WordPress.NoHardcodedTableNames warnPrefix true

// WARNING: $wpdb->prefix interpolation with warnPrefix enabled.
$sql = "SELECT * FROM {$wpdb->prefix}custom_table WHERE id = 1";

// OK: $wpdb->posts (not prefix).
$sql = "SELECT * FROM {$wpdb->posts} WHERE id = 1";

// phpcs:set Apermo.WordPress.NoHardcodedTableNames warnPrefix false
