<?php
// phpcs:disable WordPress,Squiz,PSR1,Apermo,Generic,PSR2,PEAR,Universal
// phpcs:disable SlevomatCodingStandard.TypeHints,SlevomatCodingStandard.Namespaces,SlevomatCodingStandard.PHP
// phpcs:disable SlevomatCodingStandard.ControlStructures

// Multi-line call without trailing comma should be flagged (line 7).
some_function(
	'arg1',
	'arg2'
);

// Multi-line call with trailing comma should be allowed (line 13).
some_function(
	'arg1',
	'arg2',
);

// Single-line call — not checked (line 19).
some_function( 'arg1', 'arg2' );
