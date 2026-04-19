<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Generic,PSR2,PSR12,PEAR,Universal,Apermo
// phpcs:enable Apermo.Commenting.DocCommentDescription

// @see should satisfy the short description (line 6).
/** @see wp-includes/post-template.php */
$see = 1;

// @phpstan-ignore-next-line should satisfy (line 10).
/** @phpstan-ignore-next-line OB level can be 0. */
$phpstan = 2;

// @phpstan-ignore should satisfy (line 14).
/** @phpstan-ignore argument.type */
$phpstan2 = 3;

// @param without short description should be flagged (line 18).
/** @param string $foo */
$param = 4;

// Normal short description should pass (line 22).
/** Short description. */
$normal = 5;

// Empty doc comment should not be flagged (line 26).
/** */
$empty = 6;
