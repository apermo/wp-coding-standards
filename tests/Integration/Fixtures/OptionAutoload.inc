<?php
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable

// WARNING: missing autoload parameter.
add_option( 'key', 'val' );

// OK: has autoload parameter.
add_option( 'key', 'val', '', false );

// WARNING: missing autoload parameter.
update_option( 'key', 'val' );

// OK: has autoload parameter.
update_option( 'key', 'val', false );
