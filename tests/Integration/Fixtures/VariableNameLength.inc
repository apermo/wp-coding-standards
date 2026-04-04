<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 4
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Generic,PSR2,PEAR,Universal
// phpcs:disable Apermo.PHP,Apermo.WhiteSpace,Apermo.Formatting,Apermo.Arrays,Apermo.Hooks
// phpcs:disable Apermo.Functions,Apermo.DataStructures,Apermo.WordPress
// Short variable name should warn (line 6).
$x = 1;
// Allowed short name should pass (line 8).
$id = 42;
// Long enough name should pass (line 10).
$count = 5;
// $ids should be allowed by default (line 12).
$ids = [ 1, 2, 3 ];
// $ip should be allowed by default (line 14).
$ip = '127.0.0.1';
// $ttl should be allowed by default (line 16).
$ttl = 3600;
// $uri should be allowed by default (line 18).
$uri = '/path';
