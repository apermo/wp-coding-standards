<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Universal.UseStatements,Apermo,Generic.CodeAnalysis

$value = 1;

// Yoda condition — should be flagged (line 7).
if ( 1 === $value ) {}
// Normal condition — should be allowed (line 9).
if ( $value === 1 ) {}
