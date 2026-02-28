<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Apermo,Generic,PSR2,PEAR
// 'and' should be flagged (line 4).
$a = true and false;
// 'or' should be flagged (line 6).
$b = true or false;
// && should be allowed (line 8).
$c = true && false;
// || should be allowed (line 10).
$d = true || false;
