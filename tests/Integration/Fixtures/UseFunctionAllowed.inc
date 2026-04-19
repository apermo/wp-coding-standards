<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard.TypeHints,SlevomatCodingStandard.Functions
// phpcs:disable SlevomatCodingStandard.Variables,SlevomatCodingStandard.Classes,SlevomatCodingStandard.Complexity
// phpcs:disable SlevomatCodingStandard.Arrays,SlevomatCodingStandard.Operators,SlevomatCodingStandard.PHP
// phpcs:disable SlevomatCodingStandard.ControlStructures,Generic,PSR2,PSR12,PEAR
// phpcs:disable Universal.Operators,Universal.Arrays,Universal.ControlStructures
// phpcs:disable Universal.FunctionDeclarations
// phpcs:disable Apermo
// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses
// phpcs:disable SlevomatCodingStandard.Namespaces.AlphabeticallySortedUses

namespace Apermo\Test;

// use function should be allowed (line 15).
use function Activitypub\get_webfinger_resource;

// use const should still be disallowed (line 18).
use const Activitypub\PLUGIN_VERSION;
