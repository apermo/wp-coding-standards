# Code Review Style Guide

## Project Context

This is a `phpcodesniffer-standard` Composer package providing a shared PHPCS ruleset (`Apermo`) for WordPress projects. It combines WordPress Coding Standards, Slevomat type hints, YoastCS, and PHPCompatibility with custom sniffs.

## Code Style

- Flag inline comments that merely restate what the code does instead of explaining intent or reasoning.
- Flag commented-out code.
- Do not flag docblocks — these may be required by coding standards even when the function is self-explanatory.
- Flag new code that duplicates existing functionality in the repository.
- Every PHP file must start with `declare(strict_types=1);` after the opening `<?php` tag. Exception: PHPCS test fixtures (`tests/**/Fixtures/*.inc`) are intentionally minimal and omit it.
- Prefer post-increment (`$var++`) over pre-increment (`++$var`).
- In namespaced code, fully qualify PHP native functions (`\strlen()`, `\in_array()`) for performance. Do not fully qualify WordPress functions (`plugin_dir_path()`, `wp_remote_get()`) — it breaks mocking in unit tests.

## File Operations

- Flag files that appear to be deleted and re-added as new files instead of being moved/renamed (losing git history).

## Build & Packaging

- Flag newly added files or directories that are missing from build/packaging configs (`.gitattributes`, `.drone.yml`, `Makefile`, CI workflows, etc.).

## Testing

- This project uses TDD: tests are written before implementation.
- If tests exist for a changed area, flag missing or insufficient test coverage for new/modified code.
- Custom sniffs require unit tests (extending `AbstractSniffUnitTest`) with `.inc` fixtures and optionally `.inc.fixed` for auto-fixable sniffs.
- Ruleset configuration changes require integration tests in `tests/Integration/`.

## Documentation

- If a change affects user-facing behavior, flag missing updates to README, CHANGELOG, or inline docblocks.
- CHANGELOG follows [Keep a Changelog](https://keepachangelog.com/) format.

## Commits

- This project uses Conventional Commits with a 50-char subject / 72-char body limit.
- Each commit should address a single concern.
- Types: feat, fix, docs, style, refactor, test, chore, perf.
