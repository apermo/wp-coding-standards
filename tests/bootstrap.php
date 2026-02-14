<?php
/**
 * Bootstrap file for running Apermo PHPCS unit tests.
 *
 * @package Apermo\Tests
 */

use PHP_CodeSniffer\Autoload;
use PHP_CodeSniffer\Util\Standards;

if ( defined( 'PHP_CODESNIFFER_IN_TESTS' ) === false ) {
	define( 'PHP_CODESNIFFER_IN_TESTS', true );
}

// Load the PHPCS files.
$phpcs_dir = dirname( __DIR__ ) . '/vendor/squizlabs/php_codesniffer';

if ( file_exists( $phpcs_dir . '/autoload.php' ) === false
	|| file_exists( $phpcs_dir . '/tests/bootstrap.php' ) === false
) {
	echo 'Cannot find PHPCS. Run `composer install` first.' . PHP_EOL;
	exit( 1 );
}

require_once $phpcs_dir . '/autoload.php';
require_once $phpcs_dir . '/tests/bootstrap.php';

/*
 * Set PHPCS_IGNORE_TESTS to only run Apermo tests.
 */
$all_standards   = Standards::getInstalledStandards();
$all_standards[] = 'Generic';

$standards_to_ignore = [];
foreach ( $all_standards as $standard ) {
	if ( $standard === 'Apermo' ) {
		continue;
	}

	$standards_to_ignore[] = $standard;
}

putenv( 'PHPCS_IGNORE_TESTS=' . implode( ',', $standards_to_ignore ) );

/*
 * Initialize globals required by AbstractSniffUnitTest.
 */
$GLOBALS['PHP_CODESNIFFER_SNIFF_CODES']      = [];
$GLOBALS['PHP_CODESNIFFER_FIXABLE_CODES']    = [];
$GLOBALS['PHP_CODESNIFFER_SNIFF_CASE_FILES'] = [];
$GLOBALS['PHP_CODESNIFFER_STANDARD_DIRS']    = [];
$GLOBALS['PHP_CODESNIFFER_TEST_DIRS']        = [];

/*
 * Register Apermo standard and discover test classes.
 *
 * Globals are populated without loading test files so that PHPUnit's
 * TestSuiteLoader can detect newly-declared classes when it includes them.
 */
$apermo_path = dirname( __DIR__ ) . '/Apermo';
$tests_dir   = $apermo_path . '/Tests/';

Autoload::addSearchPath( $apermo_path, 'Apermo' );

if ( is_dir( $tests_dir ) ) {
	$di = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $tests_dir ) );

	foreach ( $di as $file ) {
		if ( substr( $file->getFilename(), 0, 1 ) === '.' ) {
			continue;
		}

		$parts = explode( '.', $file->getFilename() );
		$ext   = array_pop( $parts );
		if ( $ext !== 'php' ) {
			continue;
		}

		// Derive FQCN from the file path without loading the file.
		$relative   = substr( $file->getPathname(), strlen( $tests_dir ) );
		$class_name = 'Apermo\\Tests\\' . str_replace(
			[ '/', '.php' ],
			[ '\\', '' ],
			$relative
		);

		$GLOBALS['PHP_CODESNIFFER_STANDARD_DIRS'][ $class_name ] = $apermo_path;
		$GLOBALS['PHP_CODESNIFFER_TEST_DIRS'][ $class_name ]     = $tests_dir;
	}
}

// Clean up.
unset( $phpcs_dir, $all_standards, $standards_to_ignore, $standard, $apermo_path, $tests_dir, $di, $file, $parts, $ext, $class_name, $relative );
