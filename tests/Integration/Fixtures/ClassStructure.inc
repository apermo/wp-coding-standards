<?php
// phpcs:set Apermo.NamingConventions.MinimumVariableNameLength minLength 1
// phpcs:disable SlevomatCodingStandard.TypeHints
// phpcs:disable SlevomatCodingStandard.Classes.ClassLength

// ERROR: method before property violates class structure.
class BadOrder {
	public function do_something() {
		return true;
	}

	public $name = '';
}

// OK: correct ordering (properties before methods).
class GoodOrder {
	public $name = '';

	public function do_something() {
		return true;
	}
}
