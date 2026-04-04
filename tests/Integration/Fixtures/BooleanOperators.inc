<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard.TypeHints,SlevomatCodingStandard.Functions
// phpcs:disable SlevomatCodingStandard.Variables,SlevomatCodingStandard.Namespaces,SlevomatCodingStandard.Classes
// phpcs:disable SlevomatCodingStandard.Complexity,SlevomatCodingStandard.Arrays,SlevomatCodingStandard.Operators
// phpcs:disable SlevomatCodingStandard.PHP,SlevomatCodingStandard.ControlStructures.RequireNullCoalesceOperator
// phpcs:disable Generic.Arrays,Generic.PHP,Generic.Formatting,Generic.ControlStructures,Generic.WhiteSpace
// phpcs:disable PSR2,PSR12.Classes,PSR12.Files,PSR12.Namespaces,PSR12.Properties,PSR12.Keywords
// phpcs:disable PEAR,Universal,Apermo

$a = true;
$b = false;
$c = true;

// Operator at end of line — should be flagged (line 15).
if (
	$a &&
	$b
) {
	echo 1;
}

// Operator at start of line — should be allowed (line 23).
if (
	$a
	&& $b
) {
	echo 1;
}

// Multi-line condition not fully split — should be flagged (line 33).
// phpcs:disable PSR12.ControlStructures.BooleanOperatorPlacement
// phpcs:disable Generic.CodeAnalysis.RequireExplicitBooleanOperatorPrecedence
if (
	$a && $b
	&& $c
) {
	echo 1;
}
// phpcs:enable Generic.CodeAnalysis.RequireExplicitBooleanOperatorPrecedence
// phpcs:enable PSR12.ControlStructures.BooleanOperatorPlacement

// Multi-line fully split — should be allowed (line 43).
if (
	$a
	&& $b
	&& $c
) {
	echo 1;
}

// Mixed operators without parentheses — should be flagged (line 53).
// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireMultiLineCondition
$d = $a && $b || $c;
// phpcs:enable SlevomatCodingStandard.ControlStructures.RequireMultiLineCondition

// Mixed operators with parentheses — should be allowed (line 58).
// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireMultiLineCondition
$e = ( $a && $b ) || $c;
// phpcs:enable SlevomatCodingStandard.ControlStructures.RequireMultiLineCondition
