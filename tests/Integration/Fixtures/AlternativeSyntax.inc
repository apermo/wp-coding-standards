<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Apermo,Generic,PSR2,PEAR
$x = true;
// Alternative if/endif should be flagged (line 5).
if ( $x ) :
	echo 'yes';
endif;
// Standard braces should be allowed (line 9).
if ( $x ) {
	echo 'yes';
}
