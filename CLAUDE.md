# CLAUDE.md — apermo/apermo-coding-standards

Shared PHPCS coding standards package for WordPress projects by Apermo.

GitHub: https://github.com/apermo/apermo-coding-standards

## Overview

This is a `phpcodesniffer-standard` Composer package. It provides a reusable PHPCS ruleset (`Apermo`) that combines WordPress Coding Standards, Slevomat type hints, YoastCS, and PHPCompatibility.

Projects consume it by requiring `apermo/apermo-coding-standards` and referencing `<rule ref="Apermo"/>` in their `phpcs.xml`.

## Package Structure

```
Apermo/
├── ruleset.xml          # The shared PHPCS ruleset
└── Sniffs/              # Custom sniffs (auto-discovered by PHPCS)
    └── .gitkeep
```

PHPCS auto-discovers the standard by its directory name (`Apermo/`). The `dealerdirect/phpcodesniffer-composer-installer` plugin registers installed paths automatically.

## Custom Sniffs

To add a custom sniff:

1. Create a PHP class in `Apermo/Sniffs/<Category>/<SniffName>Sniff.php`
2. The class must implement `PHP_CodeSniffer\Sniffs\Sniff`
3. PHPCS discovers it automatically by convention

Example: `Apermo/Sniffs/Naming/FunctionPrefixSniff.php` is referenced as `Apermo.Naming.FunctionPrefix` in ruleset XML.

## Testing Changes

```bash
# Install dependencies
composer install

# Run all tests (unit + integration)
composer test

# Run only unit tests (per-sniff)
vendor/bin/phpunit --testsuite=Apermo

# Run only integration tests (full ruleset)
vendor/bin/phpunit --testsuite=Integration

# Test against a sample PHP file
vendor/bin/phpcs --standard=Apermo /path/to/test-file.php

# Debug a fixture manually
vendor/bin/phpcs --standard=Apermo tests/Integration/Fixtures/ArraySyntax.inc -s

# Verify the standard is registered
vendor/bin/phpcs -i
```

### Integration Tests

Located in `tests/Integration/`, these verify that `Apermo/ruleset.xml` configuration (exclusions, severity overrides, property settings) produces the expected errors and warnings when the full standard is applied.

Each test has a minimal `.inc` fixture in `tests/Integration/Fixtures/` and a corresponding test method in `RulesetIntegrationTest.php`.

**Adding a new ruleset test:** Create a fixture file + add a test method. No existing tests are affected.

## Workflow

- After completing each task (feature, fix, etc.), create an atomic commit immediately.
- Do not batch multiple tasks into a single commit.
- When a commit resolves a GitHub issue, include `Closes #<number>` in the commit body so the issue is auto-closed when the PR is merged.

## Releasing

1. Commit changes, push to `main`
2. Tag a release: `git tag v1.0.0 && git push --tags`
3. Consumer projects update via `composer update apermo/apermo-coding-standards`
