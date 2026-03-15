<?php
// phpcs:disable WordPress,Squiz,PSR1,Generic,PSR2,PEAR,Universal
// phpcs:disable Apermo.PHP,Apermo.WhiteSpace,Apermo.Formatting,Apermo.Arrays,Apermo.Hooks
// phpcs:disable Apermo.Functions,Apermo.DataStructures,Apermo.WordPress,Apermo.NamingConventions
// phpcs:disable Apermo.CodeQuality,Apermo.Operators
// phpcs:disable SlevomatCodingStandard.Namespaces.AlphabeticallySortedUses
// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses
// phpcs:disable SlevomatCodingStandard.TypeHints
// phpcs:disable SlevomatCodingStandard.Variables,SlevomatCodingStandard.Functions
// phpcs:disable SlevomatCodingStandard.Classes,SlevomatCodingStandard.Complexity
// phpcs:disable SlevomatCodingStandard.ControlStructures,SlevomatCodingStandard.Arrays
// phpcs:disable SlevomatCodingStandard.PHP,SlevomatCodingStandard.Operators

namespace App\Test;

// use from same namespace — flagged (line 17).
use App\Test\Helper;
// Properly imported class — allowed (line 19).
use App\Service\Logger;

// Inline FQN instead of use import — flagged (line 22).
$obj = new \App\Service\Mailer();
// Global function without backslash — flagged (line 24).
$exists = function_exists( 'my_function' );
// Global constant without backslash — flagged (line 26).
$version = PHP_VERSION;
// Imported class — allowed (line 28).
$logger = new Logger();
