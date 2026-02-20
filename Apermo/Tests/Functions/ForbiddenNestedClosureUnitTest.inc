<?php

// OK: top-level closure.
$fn = function () {
	return 1;
};

// OK: top-level arrow function.
$fn = fn() => 1;

// OK: closure inside a named function.
function my_function() {
	$fn = function () {
		return 1;
	};
}

// OK: arrow function inside a named function.
function my_other_function() {
	$fn = fn() => 1;
}

// Warning: closure inside closure.
$fn = function () {
	$inner = function () {
		return 1;
	};
};

// Warning: arrow function inside closure.
$fn = function () {
	$inner = fn() => 1;
};

// Warning: closure inside arrow function.
$fn = fn() => function () {
	return 1;
};

// Warning: arrow function inside arrow function.
$fn = fn() => fn() => 1;
