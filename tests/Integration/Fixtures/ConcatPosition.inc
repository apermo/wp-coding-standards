<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Universal.UseStatements,Apermo,Generic

$a = 'hello';
$b = 'world';
// Concat at end of line — should be flagged (lines 8-9).
$bad = $a .
	$b .
	$a;
// Concat at start of line — should be allowed (lines 12-13).
$good = $a
	. $b
	. $a;
