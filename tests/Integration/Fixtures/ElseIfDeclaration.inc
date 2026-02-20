<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Universal.UseStatements,Apermo,Generic

$x = true;

if ( $x ) {
	echo 'a';
} elseif ( ! $x ) {
	echo 'b';
} else if ( $x ) {
	echo 'c';
}
