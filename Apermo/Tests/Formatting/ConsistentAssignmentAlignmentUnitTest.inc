<?php

// OK: all single-space assignments.
$a = 1;
$bb = 2;
$ccc = 3;

// OK: all aligned assignments.
$a   = 1;
$bb  = 2;
$ccc = 3;

// Warning: mixed alignment in group (majority is single-space).
$short = 1;
$medium = 2;
$veryLongVarName = 3;
$x               = 4;

// OK: single assignment, no group.
$solo = 1;

// OK: blank line separates into two groups.
$a = 1;

$b = 2;

// OK: non-assignment statement breaks the group.
$a = 1;
echo $a;
$b = 2;

// Warning: mixed alignment (majority is aligned).
$first   = 1;
$second  = 2;
$third   = 3;
$x = 4;

// OK: for loop assignments are skipped.
for ( $i = 0; $i < 10; $i += 1 ) {
	$x = $i;
}

// OK: compound assignment operators.
$a += 1;
$bb += 2;
$ccc += 3;

// Warning: mixed compound assignment alignment.
$a   .= 'x';
$bb  .= 'y';
$ccc .= 'z';
$d = 'w';
