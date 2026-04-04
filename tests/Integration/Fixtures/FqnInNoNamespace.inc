<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard.TypeHints,SlevomatCodingStandard.Functions
// phpcs:disable SlevomatCodingStandard.Variables,SlevomatCodingStandard.Classes,SlevomatCodingStandard.Complexity
// phpcs:disable SlevomatCodingStandard.Arrays,SlevomatCodingStandard.Operators,SlevomatCodingStandard.PHP
// phpcs:disable SlevomatCodingStandard.ControlStructures,Generic,PSR2,PSR12,PEAR,Universal,Apermo

// No namespace — FQN should be allowed (line 8).
try {
	throw new \RuntimeException( 'test' );
} catch ( \Throwable $error ) {
	echo $error->getMessage();
}
