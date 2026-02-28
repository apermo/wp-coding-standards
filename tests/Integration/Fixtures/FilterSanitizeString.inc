<?php
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// ERROR: deprecated constant.
$val = filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING );

// OK: valid constant.
$val = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL );
