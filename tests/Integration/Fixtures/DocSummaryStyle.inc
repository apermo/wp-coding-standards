<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Generic,PSR2,PSR12,PEAR,Universal,Apermo
// phpcs:enable Apermo.Commenting.DocSummaryStyle

// Third-person singular — OK (line 6).
/** Displays the post title. */
function a(): void {}

// Whitelist "Callback" — OK (line 10).
/** Callback for the save_post action. */
function b(): void {}

// Irregular third-person "Is" — OK (line 14).
/** Is true when the option is set. */
function c(): void {}

// AntiPattern "Allows you to" (line 18).
/** Allows you to modify the post content. */
function d(): void {}

// AntiPattern "Lets you" (line 22).
/** Lets you register custom post types. */
function e(): void {}

// BareInfinitive "Process" (line 26).
/** Process the queue. */
function f(): void {}

// BareInfinitive "Fix" (line 30).
/** Fix the offset. */
function g(): void {}

// NotThirdPerson "Display" (line 34).
/** Display the date. */
function h(): void {}

// NotThirdPerson "Get" (line 38).
/** Get the option value. */
function i(): void {}

// @param tag first — skip (line 42).
/** @param string $x */
$param = 1;

// {@inheritDoc} — skip (line 46).
/** {@inheritDoc} */
function inh(): void {}

// Empty — skip (line 50).
/** */
function emp(): void {}

// Backtick code ref stripped; third-person "handles" — OK (line 54).
/** `wp_foo()` handles the request. */
function code(): void {}

// Property docblock — noun-form summary skipped (line 59).
class HasProperties {
	/** The user's display name. */
	public string $name = '';
}

// Constant docblock — noun-form summary skipped (line 64).
/** Maximum retry attempts. */
const MAX_RETRIES = 3;

// Bare variable with noun-form docblock skipped (line 68).
/** The current timestamp. */
$now = 0;

// @param tag first on a function (not a property) — skipped, no summary to check.
/** @param string $x */
function tag_first_fn( string $x ): void {}

// Summary is only a backtick-quoted code reference — cleaned content is empty, skip.
/** `wp_foo()` */
function only_code_ref(): void {}

// Summary contains no letters (only digits and punctuation) — skip.
/** 123 — ??? */
function no_letters(): void {}

// Two back-to-back docblocks: first has no declaration before the next docblock opens.
/** Runs first. */
/** Displays the result. */
function after_two_docs(): void {}

// Trailing docblock at EOF with no attached declaration — walks off the end.
/** Ends here. */
