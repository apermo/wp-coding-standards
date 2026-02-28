<?php
// phpcs:disable WordPress,Squiz,PSR1,Apermo,Generic,PSR2,PEAR
// phpcs:disable SlevomatCodingStandard.TypeHints,SlevomatCodingStandard.Namespaces,SlevomatCodingStandard.PHP
// phpcs:disable SlevomatCodingStandard.ControlStructures,SlevomatCodingStandard.Functions.RequireTrailingCommaInCall
// phpcs:disable Universal

// Non-static closure not using $this should be flagged (line 7).
$fn = function () {
	return 1;
};

// Static closure should be allowed (line 12).
$fn = static function () {
	return 1;
};
