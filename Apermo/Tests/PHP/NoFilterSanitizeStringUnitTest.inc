<?php
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// ERROR: deprecated FILTER_SANITIZE_STRING.
$value = filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING );

// ERROR: deprecated FILTER_SANITIZE_STRIPPED (alias).
$value = filter_var( $input, FILTER_SANITIZE_STRIPPED );

// ERROR: deprecated FILTER_SANITIZE_MAGIC_QUOTES.
$value = filter_input( INPUT_GET, 'data', FILTER_SANITIZE_MAGIC_QUOTES );

// OK: valid filter constant.
$value = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL );

// OK: valid filter constant.
$value = filter_var( $input, FILTER_SANITIZE_NUMBER_INT );

// OK: used as a string in a different context (method name).
$obj->FILTER_SANITIZE_STRING();

// OK: static method call.
Filters::FILTER_SANITIZE_STRING();

// OK: nullsafe method call.
$obj?->FILTER_SANITIZE_STRING();

// OK: function definition.
function FILTER_SANITIZE_STRING() {
	return true;
}
