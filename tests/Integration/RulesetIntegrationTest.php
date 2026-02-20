<?php
/**
 * Integration tests for the Apermo PHPCS ruleset.
 *
 * Verifies that ruleset.xml configuration (exclusions, severity overrides,
 * property settings) produces the expected errors and warnings when the
 * full Apermo standard is applied to fixture files.
 *
 * @package Apermo\Tests\Integration
 */

namespace Apermo\Tests\Integration;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Tests\ConfigDouble;
use PHPUnit\Framework\TestCase;

class RulesetIntegrationTest extends TestCase {

	private static Ruleset $ruleset;

	private static ConfigDouble $config;

	private static string $fixtures_dir;

	public static function setUpBeforeClass(): void {
		self::$fixtures_dir = __DIR__ . '/Fixtures/';

		// Read installed_paths directly from CodeSniffer.conf because ConfigDouble
		// (used by unit tests) clears the static config data cache.
		$config_file = dirname( __DIR__, 2 ) . '/vendor/squizlabs/php_codesniffer/CodeSniffer.conf';
		$installed_paths = '';
		if ( file_exists( $config_file ) ) {
			include $config_file;
			$installed_paths = $phpCodeSnifferConfig['installed_paths'] ?? '';
		}

		self::$config = new ConfigDouble( [
			'--standard=Apermo',
			'--runtime-set',
			'installed_paths',
			$installed_paths,
		] );

		self::$ruleset = new Ruleset( self::$config );
	}

	/**
	 * Process a fixture file through the full Apermo ruleset.
	 *
	 * @param string $fixture_name Fixture filename (e.g. "ArraySyntax.inc").
	 *
	 * @return LocalFile The processed file object.
	 */
	private function processFixture( string $fixture_name ): LocalFile {
		$file = new LocalFile(
			self::$fixtures_dir . $fixture_name,
			self::$ruleset,
			self::$config
		);
		$file->process();
		return $file;
	}

	/**
	 * Assert that a specific line has at least one error from a sniff whose source contains the given substring.
	 */
	private function assertErrorOnLine( LocalFile $file, int $line, string $source_contains, string $message = '' ): void {
		$errors = $file->getErrors();
		$this->assertArrayHasKey( $line, $errors, $message ?: "Expected an error on line {$line}." );

		$sources = $this->collectSources( $errors[ $line ] );
		$found   = $this->sourceContains( $sources, $source_contains );
		$this->assertTrue( $found, $message ?: "Expected an error containing '{$source_contains}' on line {$line}. Found: " . implode( ', ', $sources ) );
	}

	/**
	 * Assert that a specific line has at least one warning from a sniff whose source contains the given substring.
	 */
	private function assertWarningOnLine( LocalFile $file, int $line, string $source_contains, string $message = '' ): void {
		$warnings = $file->getWarnings();
		$this->assertArrayHasKey( $line, $warnings, $message ?: "Expected a warning on line {$line}." );

		$sources = $this->collectSources( $warnings[ $line ] );
		$found   = $this->sourceContains( $sources, $source_contains );
		$this->assertTrue( $found, $message ?: "Expected a warning containing '{$source_contains}' on line {$line}. Found: " . implode( ', ', $sources ) );
	}

	/**
	 * Assert that a specific line has no errors.
	 */
	private function assertNoErrorsOnLine( LocalFile $file, int $line, string $message = '' ): void {
		$errors = $file->getErrors();
		$this->assertArrayNotHasKey( $line, $errors, $message ?: "Expected no errors on line {$line}." );
	}

	/**
	 * Assert that a specific line has no warnings.
	 */
	private function assertNoWarningsOnLine( LocalFile $file, int $line, string $message = '' ): void {
		$warnings = $file->getWarnings();
		$this->assertArrayNotHasKey( $line, $warnings, $message ?: "Expected no warnings on line {$line}." );
	}

	/**
	 * Collect all sniff source codes from a line's violations.
	 *
	 * @param array $columns Column-indexed array of violations.
	 *
	 * @return string[] Flat list of source codes.
	 */
	private function collectSources( array $columns ): array {
		$sources = [];
		foreach ( $columns as $violations ) {
			foreach ( $violations as $violation ) {
				$sources[] = $violation['source'];
			}
		}
		return $sources;
	}

	/**
	 * Check if any source string contains the given substring.
	 */
	private function sourceContains( array $sources, string $substring ): bool {
		foreach ( $sources as $source ) {
			if ( str_contains( $source, $substring ) ) {
				return true;
			}
		}
		return false;
	}

	// -------------------------------------------------------------------------
	// Test methods — one per ruleset behavior.
	// -------------------------------------------------------------------------

	public function testShortArraySyntaxAllowed_LongArrayFlagged(): void {
		$file = $this->processFixture( 'ArraySyntax.inc' );
		$this->assertNoErrorsOnLine( $file, 4, 'Short array syntax should be allowed.' );
		$this->assertErrorOnLine( $file, 6, 'DisallowLongArraySyntax', 'Long array() should be flagged.' );
	}

	public function testYodaConditionsDisallowed(): void {
		$file = $this->processFixture( 'YodaConditions.inc' );
		$this->assertErrorOnLine( $file, 7, 'DisallowYodaConditions', 'Yoda condition should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 9, 'Normal condition should be allowed.' );
	}

	public function testElseIfOverElseIf(): void {
		$file = $this->processFixture( 'ElseIfDeclaration.inc' );
		$this->assertErrorOnLine( $file, 10, 'ElseIfDeclaration', '"else if" should be an error.' );
		$this->assertNoErrorsOnLine( $file, 8, '"elseif" should be allowed.' );
	}

	public function testClosureLengthLimit(): void {
		$file = $this->processFixture( 'ClosureLength.inc' );
		$this->assertNoErrorsOnLine( $file, 4, 'A 5-line closure should be allowed.' );
		$this->assertNoWarningsOnLine( $file, 4, 'A 5-line closure should not warn.' );
		$this->assertErrorOnLine( $file, 11, 'NoLongClosures', 'A >5-line closure should be flagged.' );
	}

	public function testForbiddenFunctions(): void {
		$file = $this->processFixture( 'ForbiddenFunctions.inc' );
		$this->assertErrorOnLine( $file, 8, 'ForbiddenFunctions', 'sizeof() should be forbidden.' );
		$this->assertErrorOnLine( $file, 9, 'ForbiddenFunctions', 'intval() should be forbidden.' );
		$this->assertErrorOnLine( $file, 10, 'ForbiddenFunctions', 'floatval() should be forbidden.' );
		$this->assertErrorOnLine( $file, 11, 'ForbiddenFunctions', 'strval() should be forbidden.' );
		$this->assertErrorOnLine( $file, 12, 'ForbiddenFunctions', 'boolval() should be forbidden.' );
	}

	public function testTypeHintsEnforced(): void {
		$file = $this->processFixture( 'TypeHints.inc' );
		$this->assertErrorOnLine( $file, 10, 'ReturnTypeHint', 'Missing return type hint should be flagged.' );
		$this->assertErrorOnLine( $file, 16, 'ParameterTypeHint', 'Missing parameter type hint should be flagged.' );
	}

	public function testStdClassDiscouraged(): void {
		$file = $this->processFixture( 'StdClassUsage.inc' );
		$this->assertWarningOnLine( $file, 7, 'ForbiddenClasses', 'stdClass usage should warn.' );
	}

	public function testShortTernaryAllowed(): void {
		$file = $this->processFixture( 'ShortTernary.inc' );
		$this->assertNoErrorsOnLine( $file, 6, 'Short ternary should be allowed.' );
		$this->assertNoWarningsOnLine( $file, 6, 'Short ternary should not warn.' );
	}

	public function testObjectCastWarned(): void {
		$file = $this->processFixture( 'ObjectCast.inc' );
		$this->assertWarningOnLine( $file, 6, 'ForbiddenObjectCast', '(object) cast should produce a warning.' );
	}

	public function testRequireOverInclude(): void {
		$file = $this->processFixture( 'RequireOverInclude.inc' );
		$this->assertErrorOnLine( $file, 4, 'RequireNotInclude', 'include should be flagged in favour of require.' );
		$this->assertNoErrorsOnLine( $file, 7, 'require should be allowed.' );
	}

	public function testUseStatements(): void {
		$file = $this->processFixture( 'UseStatements.inc' );
		$this->assertErrorOnLine( $file, 7, 'UnusedUses', 'Unused use statement should be flagged.' );
		$this->assertErrorOnLine( $file, 9, 'DisallowUseConst', 'use const should be flagged.' );
		$this->assertErrorOnLine( $file, 11, 'DisallowUseFunction', 'use function should be flagged.' );
	}

	public function testConcatAtStartOfLine(): void {
		$file = $this->processFixture( 'ConcatPosition.inc' );
		$this->assertErrorOnLine( $file, 7, 'ConcatPosition', 'Concat at end of line should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 12, 'Concat at start of line should be allowed.' );
	}
}
