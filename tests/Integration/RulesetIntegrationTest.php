<?php
declare(strict_types=1);

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

	private static string $installed_paths;

	public static function setUpBeforeClass(): void {
		self::$fixtures_dir = __DIR__ . '/Fixtures/';

		// Read installed_paths directly from CodeSniffer.conf because ConfigDouble
		// (used by unit tests) clears the static config data cache.
		$config_file = dirname( __DIR__, 2 ) . '/vendor/squizlabs/php_codesniffer/CodeSniffer.conf';
		self::$installed_paths = '';
		if ( file_exists( $config_file ) ) {
			include $config_file;
			self::$installed_paths = $phpCodeSnifferConfig['installed_paths'] ?? '';
		}

		self::$config = new ConfigDouble( [
			'--standard=Apermo',
			'--runtime-set',
			'installed_paths',
			self::$installed_paths,
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

	public function testTextDomainValidation(): void {
		$config = new ConfigDouble( [
			'--standard=Apermo',
			'--runtime-set',
			'installed_paths',
			self::$installed_paths,
			'--runtime-set',
			'text_domain',
			'test-domain',
		] );

		$ruleset = new Ruleset( $config );
		$file    = new LocalFile(
			self::$fixtures_dir . 'TextDomain.inc',
			$ruleset,
			$config,
		);
		$file->process();

		$this->assertErrorOnLine( $file, 5, 'TextDomainMismatch', 'Wrong text domain should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 7, 'Correct text domain should be allowed.' );
	}

	public function testMinimumVariableNameLength(): void {
		$file = $this->processFixture( 'VariableNameLength.inc' );
		$this->assertWarningOnLine( $file, 7, 'MinimumVariableNameLength', 'Short variable should warn.' );
		$this->assertNoWarningsOnLine( $file, 9, 'Allowed short name should pass.' );
		$this->assertNoWarningsOnLine( $file, 11, 'Long enough name should pass.' );
	}

	public function testExitUsage(): void {
		$file = $this->processFixture( 'ExitUsage.inc' );
		$this->assertErrorOnLine( $file, 7, 'ExitUsage', 'die should be flagged.' );
		$this->assertErrorOnLine( $file, 9, 'ExitUsage', 'die() should be flagged.' );
		$this->assertErrorOnLine( $file, 11, 'ExitUsage', 'bare exit should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 13, 'exit() should be allowed.' );
	}

	public function testUnusedVariable(): void {
		$file = $this->processFixture( 'UnusedVariable.inc' );
		$this->assertErrorOnLine( $file, 9, 'UnusedVariable', 'Unused variable should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 15, 'Used variable should be allowed.' );
		$this->assertNoErrorsOnLine( $file, 23, 'Unused foreach value with used key should be allowed.' );
	}

	public function testClassLength(): void {
		$file = $this->processFixture( 'ClassLength.inc' );
		$this->assertNoErrorsOnLine( $file, 8, 'Short class should be allowed.' );
		$this->assertErrorOnLine( $file, 13, 'ClassLength', 'Long class should be flagged.' );
	}

	public function testFunctionLength(): void {
		$file = $this->processFixture( 'FunctionLength.inc' );
		$this->assertNoErrorsOnLine( $file, 9, 'Short function should be allowed.' );
		$this->assertErrorOnLine( $file, 15, 'FunctionLength', 'Long function should be flagged.' );
	}

	public function testCognitiveComplexity(): void {
		$file = $this->processFixture( 'CognitiveComplexity.inc' );
		$this->assertNoErrorsOnLine( $file, 8, 'Simple function should pass.' );
		$this->assertErrorOnLine( $file, 13, 'Cognitive', 'Complex function should be flagged.' );
	}

	public function testDisallowImplicitArrayCreation(): void {
		$file = $this->processFixture( 'ImplicitArrayCreation.inc' );
		$this->assertErrorOnLine( $file, 9, 'DisallowImplicitArrayCreation', 'Implicit array creation should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 16, 'Append to declared array should be allowed.' );
	}

	public function testStaticClosureRequired(): void {
		$file = $this->processFixture( 'StaticClosure.inc' );
		$this->assertErrorOnLine( $file, 8, 'StaticClosure', 'Non-static closure should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 13, 'Static closure should be allowed.' );
	}

	public function testRequireTrailingCommaInCall(): void {
		$file = $this->processFixture( 'TrailingCommaInCall.inc' );
		$this->assertErrorOnLine( $file, 9, 'RequireTrailingCommaInCall', 'Missing trailing comma should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 15, 'Trailing comma should be allowed.' );
	}

	public function testNullTypeHintOnLastPosition(): void {
		$file = $this->processFixture( 'NullTypeHintPosition.inc' );
		$this->assertErrorOnLine( $file, 12, 'NullTypeHintOnLastPosition', 'null|string should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 19, 'string|null should be allowed.' );
	}

	public function testLongTypeHintsFlagged(): void {
		$file = $this->processFixture( 'LongTypeHints.inc' );
		$this->assertErrorOnLine( $file, 11, 'LongTypeHints', '@param integer should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 22, '@param int should be allowed.' );
	}

	public function testRequireNullCoalesceOperator(): void {
		$file = $this->processFixture( 'NullCoalesce.inc' );
		$this->assertErrorOnLine( $file, 7, 'RequireNullCoalesceOperator', 'isset() ternary should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 9, 'Null coalesce should be allowed.' );
	}

	public function testAlternativeSyntaxDisallowed(): void {
		$file = $this->processFixture( 'AlternativeSyntax.inc' );
		$this->assertErrorOnLine( $file, 5, 'DisallowAlternativeSyntax', 'Alternative if/endif should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 9, 'Standard braces should be allowed.' );
	}

	public function testLogicalAndOrDisallowed(): void {
		$file = $this->processFixture( 'LogicalOperators.inc' );
		$this->assertErrorOnLine( $file, 4, 'DisallowLogicalAndOr', '"and" should be flagged.' );
		$this->assertErrorOnLine( $file, 6, 'DisallowLogicalAndOr', '"or" should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 8, '&& should be allowed.' );
		$this->assertNoErrorsOnLine( $file, 10, '|| should be allowed.' );
	}

	public function testRequireRestPermissionCallback(): void {
		$file = $this->processFixture( 'RestPermissionCallback.inc' );
		$this->assertErrorOnLine( $file, 8, 'RequireRestPermissionCallback', 'Missing permission_callback should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 18, 'Route with permission_callback should be allowed.' );
	}

	public function testNoFilterSanitizeString(): void {
		$file = $this->processFixture( 'FilterSanitizeString.inc' );
		$this->assertErrorOnLine( $file, 7, 'NoFilterSanitizeString', 'FILTER_SANITIZE_STRING should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 10, 'FILTER_SANITIZE_EMAIL should be allowed.' );
	}

	public function testNoQueryPosts(): void {
		$file = $this->processFixture( 'NoQueryPosts.inc' );
		$this->assertErrorOnLine( $file, 5, 'DiscouragedFunctions', 'query_posts() should be flagged.' );
	}

	public function testPreferModernStringFunctions(): void {
		$file = $this->processFixture( 'ModernStringFunctions.inc' );
		$this->assertErrorOnLine( $file, 5, 'PreferModernStringFunctions', 'strpos === false should be flagged.' );
		$this->assertErrorOnLine( $file, 8, 'PreferModernStringFunctions', 'strpos === 0 should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 11, 'strpos without comparison should be allowed.' );
	}

	public function testNoAdminAjax(): void {
		$file = $this->processFixture( 'NoAdminAjax.inc' );
		$this->assertErrorOnLine( $file, 5, 'NoAdminAjax', 'wp_ajax_ hook should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 8, 'Non-ajax hook should be allowed.' );
	}

	public function testRequireAbsoluteIncludePath(): void {
		$file = $this->processFixture( 'AbsoluteIncludePath.inc' );
		$this->assertErrorOnLine( $file, 5, 'RequireAbsoluteIncludePath', 'Relative require should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 8, '__DIR__ path should be allowed.' );
		$this->assertErrorOnLine( $file, 11, 'RequireAbsoluteIncludePath', 'Relative include should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 14, 'Constant path should be allowed.' );
	}

	public function testRequireOptionAutoload(): void {
		$file = $this->processFixture( 'OptionAutoload.inc' );
		$this->assertWarningOnLine( $file, 7, 'RequireOptionAutoload', 'add_option without autoload should warn.' );
		$this->assertWarningOnLine( $file, 13, 'RequireOptionAutoload', 'update_option without autoload should warn.' );
		$this->assertNoWarningsOnLine( $file, 10, 'add_option with autoload should be allowed.' );
		$this->assertNoWarningsOnLine( $file, 16, 'update_option with autoload should be allowed.' );
	}

	public function testSwitchToBlogRequiresRestore(): void {
		$file = $this->processFixture( 'SwitchToBlogRestore.inc' );
		$this->assertErrorOnLine( $file, 8, 'SwitchToBlogRequiresRestore', 'Missing restore should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 14, 'With restore should be allowed.' );
	}

	public function testSapiDependentFeatures(): void {
		$file = $this->processFixture( 'SapiDependentFeatures.inc' );
		$this->assertErrorOnLine( $file, 6, 'SapiDependentFeatures', 'INPUT_REQUEST should be an error.' );
		$this->assertWarningOnLine( $file, 9, 'SapiDependentFeatures', 'INPUT_SERVER should warn.' );
		$this->assertNoErrorsOnLine( $file, 12, 'INPUT_GET should be allowed.' );
		$this->assertNoWarningsOnLine( $file, 12, 'INPUT_GET should not warn.' );
	}

	public function testExcessiveParameterCount(): void {
		$file = $this->processFixture( 'ExcessiveParameterCount.inc' );
		$this->assertWarningOnLine( $file, 6, 'ExcessiveParameterCount', '7 params should warn.' );
		$this->assertNoWarningsOnLine( $file, 11, '3 params should be allowed.' );
	}

	public function testClassStructure(): void {
		$file = $this->processFixture( 'ClassStructure.inc' );
		$this->assertErrorOnLine( $file, 12, 'ClassStructure', 'Property after method should be flagged.' );
		$this->assertNoErrorsOnLine( $file, 18, 'Correct class structure should be allowed.' );
	}
}
