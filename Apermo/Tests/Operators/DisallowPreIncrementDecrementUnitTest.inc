<?php
// Post-increment — allowed.
$a++;
// Post-decrement — allowed.
$a--;
// Pre-increment — flagged.
++$a;
// Pre-decrement — flagged.
--$a;
// Pre-increment on property — flagged.
++$obj->prop;
// Pre-decrement on static property — flagged.
++self::$count;
// Pre-increment on array access — flagged.
++$arr['key'];
// Post-increment on property — allowed.
$obj->prop++;
// Post-increment on static property — allowed.
self::$count++;
// Post-increment on array access — allowed.
$arr['key']++;
