<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Universal.UseStatements,Apermo,Generic.PHP.ForbiddenFunctions
// 5-line closure — should be allowed (line 4).
$short = function () {
	$a = 1;
	$b = 2;
	$c = 3;
	return $a + $b + $c;
};
// >5-line closure — should be flagged (line 11).
$long = function () {
	$a = 1;
	$b = 2;
	$c = 3;
	$d = 4;
	$e = 5;
	return $a + $b + $c + $d + $e;
};
