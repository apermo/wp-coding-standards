<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Universal.UseStatements,Apermo,Generic

$input = 'hello';
// Short ternary — should be allowed (line 6).
$result = $input ?: 'default';
