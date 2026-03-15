<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Generic,PSR2,PEAR,Universal
// phpcs:disable Apermo.PHP,Apermo.WhiteSpace,Apermo.Formatting,Apermo.Arrays,Apermo.Hooks
// phpcs:disable Apermo.Functions,Apermo.DataStructures,Apermo.WordPress,Apermo.NamingConventions
// phpcs:disable Apermo.CodeQuality
// phpcs:enable SlevomatCodingStandard.Operators.RequireOnlyStandaloneIncrementAndDecrementOperators
// phpcs:enable Apermo.Operators.DisallowPreIncrementDecrement
$a = 1;
$b = 1;
// Pre-increment — flagged by custom sniff (line 12).
++$a;
// Post-increment standalone — allowed (line 14).
$a++;
// Post-increment in for — allowed (line 16).
for ( $i = 0; $i < 10; $i++ ) { $a++; }
// Non-standalone: assignment with increment — flagged by Slevomat (line 18).
$b = $a++;
// Post-decrement standalone — allowed (line 20).
$a--;
