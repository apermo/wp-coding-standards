<?php

namespace App\Test;

// PHP native without backslash — flagged (line 5).
$len = strlen( 'hello' );
// PHP native with backslash — allowed (line 7).
$len = \strlen( 'hello' );
// Non-native without backslash — allowed (line 9).
plugin_dir_path( __FILE__ );
// Non-native with backslash — flagged (line 11).
\plugin_dir_path( __FILE__ );
// Method call — not affected (line 13).
$obj->strlen( 'hello' );
// Static method — not affected (line 15).
Foo::bar( 1 );
// Namespaced function — not affected (line 17).
Sub\func( 1 );
// PHP native in_array without backslash — flagged (line 19).
$found = in_array( 1, [ 1, 2 ], true );
// PHP native with backslash in expression — allowed (line 21).
$found = \in_array( 1, [ 1, 2 ], true );
