<?php
// phpcs:disable WordPress,Squiz,PSR1,Apermo,Generic,PSR2,PEAR,Universal
// phpcs:disable SlevomatCodingStandard.TypeHints,SlevomatCodingStandard.Namespaces,SlevomatCodingStandard.PHP
// phpcs:disable SlevomatCodingStandard.ControlStructures,SlevomatCodingStandard.Functions
// phpcs:disable SlevomatCodingStandard.Arrays,SlevomatCodingStandard.Variables

// Simple function — should be allowed (line 8).
function simple( $x ) {
	return $x + 1;
}

// Highly complex function — should warn (line 13).
function complex( $a, $b, $c, $d ) {
	if ( $a ) {
		if ( $b ) {
			if ( $c ) {
				for ( $i = 0; $i < 10; $i++ ) {
					if ( $d ) {
						switch ( $i ) {
							case 1:
								if ( $a && $b ) {
									while ( $c ) {
										if ( $d || $a ) {
											break;
										}
										$c--;
									}
								}
								break;
							case 2:
								if ( $b || $c ) {
									foreach ( range( 1, 5 ) as $j ) {
										if ( $j > 3 && $a ) {
											continue;
										}
									}
								}
								break;
						}
					}
				}
			}
		}
	}
	return null;
}
