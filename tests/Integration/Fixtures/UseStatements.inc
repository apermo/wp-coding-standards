<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard.TypeHints,Apermo,Generic,PSR12
// phpcs:disable SlevomatCodingStandard.Namespaces.AlphabeticallySortedUses
namespace Apermo\Tests\Integration\Fixtures;

// Unused use — should be flagged (line 7).
use RuntimeException;
// use const — should be flagged (line 9).
use const PHP_EOL;
// use function — should be flagged (line 11).
use function array_map;

class UseStatementFixture {

	public function run(): void {
		echo array_map( 'intval', [] ) . PHP_EOL;
	}
}
