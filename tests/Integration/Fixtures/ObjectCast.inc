<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Universal.UseStatements,Apermo.PHP.RequireNotInclude,Apermo.WhiteSpace,Apermo.Formatting,Apermo.Hooks,Generic.PHP.ForbiddenFunctions

$data = [ 'key' => 'value' ];
// (object) cast — should produce a violation (line 6).
$obj = (object) $data;
