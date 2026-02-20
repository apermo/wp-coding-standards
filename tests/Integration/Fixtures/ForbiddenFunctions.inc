<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Universal.UseStatements,Apermo

$items = [ 'a', 'b' ];
$num   = '42';
$pi    = '3.14';
// Forbidden function-based casts (lines 8-12).
$a = sizeof( $items );
$b = intval( $num );
$c = floatval( $pi );
$d = strval( $num );
$e = boolval( $num );
