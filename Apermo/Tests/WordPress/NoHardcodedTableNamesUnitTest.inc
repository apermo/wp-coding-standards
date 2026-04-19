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

// WARNING: hardcoded CREATE TABLE target.
$sql = 'CREATE TABLE wp_custom (id INT PRIMARY KEY)';

// WARNING: hardcoded DROP TABLE IF EXISTS target.
$sql = 'DROP TABLE IF EXISTS wp_obsolete';

// WARNING: hardcoded ALTER TABLE target.
$sql = 'ALTER TABLE wp_users ADD COLUMN foo VARCHAR(50)';

// OK: HTML <table> with CSS class — not SQL.
$html = '<table class="wp-list-table widefat fixed striped">';

// OK: HTML <table> with id — not SQL.
$html = '<table id="users-table">';

// OK: English prose containing "from a" — not SQL.
$msg = 'Lessons from a distributed team are instructive.';

// OK: English prose containing "from the" — not SQL.
$msg = 'Deactivate the plugin to hide these posts from the admin.';

// OK: WP UI label starting with "Update" but no SET — not SQL.
$label = 'Update Revision Tag';

// OK: Prose mentioning "update" followed by noun — not SQL.
$msg = 'Update the configuration before deploying.';
