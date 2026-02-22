# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.0] - 2026-02-22

### Changed

- Lowered minimum PHP version from 8.3 to 7.4. Added
  `str_ends_with()` polyfill for PHP < 8.0 compatibility.
- `RequireHookDocBlock`: all violation codes (`Missing`,
  `MissingParam`, `MissingReturn`) are now errors instead
  of warnings.

### Added

- AI usage disclaimer section in README.

## [2.1.0] - 2026-02-21

### Changed

- Release workflow now publishes releases automatically
  on merge to main (no longer creates drafts).
- Removed `release-published.yml` workflow (no longer
  needed without draft releases).

### Fixed

- Release workflow now finds the merged PR by branch
  name (`release/$VERSION`) instead of SHA search.
- `RequireHookDocBlock`: empty `array ( )` with
  whitespace no longer triggers a false MissingParam
  warning in `_ref_array` and `_deprecated` variants.

### Tests

- `ArrayComplexity`: cover function calls inside arrays
  and numeric arrays nested in associative arrays.
- `MultipleEmptyLines`: cover `$stackPtr < 2` guard
  and `findNext()` returning false at EOF.
- `ImplicitPostFunction`: cover non-ID property access
  patterns (`$post->name`, `$post->id`).

## [2.0.1] - 2026-02-21

### Fixed

- Release workflow no longer attempts to commit directly
  to main. Set the release date in the PR before merging.

## [2.0.0] - 2026-02-21

### Added

- `phpcbf` auto-fix for `ConsistentAssignmentAlignment`
  and `ConsistentDoubleArrowAlignment` sniffs.
- `OverAligned` error code for both alignment sniffs:
  flags groups where operators are padded beyond the
  longest left-hand side.
- Codecov integration: test coverage reporting on
  PHP 8.4 with PCOV, uploaded on every PR and push.
- PHPStan level 6 with strict-rules for sniff source
  code, integrated into CI pipeline.
- GitHub Actions: prerelease workflow for `release/*`
  branches via `workflow_dispatch`.
- Release and PR validation workflows now derive version
  from CHANGELOG headings instead of `composer.json`.

### Changed

- Published package on [Packagist](https://packagist.org/packages/apermo/apermo-coding-standards).
  VCS repository setup is no longer needed.
- Removed `version` field from `composer.json`. Packagist
  derives versions from git tags exclusively.

## [1.4.0] - 2026-02-20

### Added

- `Generic.PHP.ForbiddenFunctions` config: forbids
  `intval()`, `floatval()`, `strval()`, and `boolval()`.
  Use `(int)`, `(float)`, `(string)`, and `(bool)` casts
  instead.
- `Apermo.Functions.ForbiddenNestedClosure` sniff: warns
  when closures or arrow functions are nested inside other
  closures or arrow functions. Extract inner callbacks to
  named functions instead.

## [1.3.1] - 2026-02-19

### Changed

- Rename Composer package from `apermo/wp-coding-standards`
  to `apermo/apermo-coding-standards`.
- Update all repository URLs in README, CLAUDE.md, and
  CHANGELOG to match the new package name.

## [1.3.0] - 2026-02-16

### Added

- `Apermo.PHP.ForbiddenObjectCast` sniff: warns on
  `(object)` casts that implicitly create `stdClass`
  instances. Use typed classes instead.
- `SlevomatCodingStandard.PHP.ForbiddenClasses` config:
  warns on `new \stdClass()` usage. Use typed classes
  instead.
- `Apermo.Hooks.RequireHookDocBlock` sniff: warns when
  WordPress hook invocations (`do_action`,
  `apply_filters`, etc.) lack a preceding PHPDoc block.
  Checks for `@param` and `@return` tags. Supports
  `_ref_array` and `_deprecated` variants.
- `Apermo.Formatting.ConsistentAssignmentAlignment`
  sniff: warns when consecutive assignment statements
  mix single-space and aligned styles. Either all `=`
  operators align to the same column, or all use a
  single space — mixing is flagged.
- `Apermo.Arrays.ConsistentDoubleArrowAlignment` sniff:
  warns when multi-line arrays mix single-space and
  aligned `=>` styles. Same consistency rule as the
  assignment alignment sniff.

### Changed

- Exclusion comments for
  `Generic.Formatting.MultipleStatementAlignment` and
  `WordPress.Arrays.MultipleStatementAlignment` now say
  "Superseded by" the new Apermo alignment sniffs.

## [1.2.1] - 2026-02-16

### Added

- GitHub Actions: CI workflow (PHPUnit on PHP 8.3 + 8.4)
- GitHub Actions: conventional commits validation
- GitHub Actions: automated draft release on push to main
- GitHub Actions: PR validation (CHANGELOG entry check)
- GitHub Actions: stale issue/PR cleanup
- Renovate bot for dependency updates

### Changed

- PR validation now requires version bump in
  `composer.json` and matching CHANGELOG entry.
- Release workflow reads version from `composer.json`
  instead of parsing CHANGELOG.md.
- Added `version`, `authors`, `homepage`, `support`,
  and `keywords` to `composer.json`.
- Track `composer.lock` for Renovate dependency
  management.

### Fixed

- Skip merge commits in conventional commit validation.
- Remove `edited` PR trigger from commit validation
  to avoid redundant runs on description changes.
- README installation section now shows VCS repository
  setup (package is not yet on Packagist).
- README `ImplicitPostFunction` docs updated to reflect
  redesigned error codes from v1.2.0.

## [1.2.0] - 2026-02-15

### Added

- `Apermo.DataStructures.ArrayComplexity` sniff: flags
  deeply nested or wide associative arrays that would
  benefit from typed objects. Configurable warning/error
  thresholds for nesting depth and key count.
- `Apermo.WordPress.GlobalPostAccess` sniff: flags
  `global $post;` inside functions, methods, closures,
  and arrow functions. Pass `WP_Post` or post ID instead.
- `Apermo.WordPress.ImplicitPostFunction` sniff: flags
  WordPress template functions (e.g. `get_the_title()`,
  `get_permalink()`) called without an explicit post
  argument inside function scopes.

### Changed

- **BREAKING:** `Apermo.WordPress.ImplicitPostFunction`
  redesigned severity tiers. Severity is now based on
  the argument passed, not the function identity.
  New error codes: `MissingArgument` (error),
  `NullArgument` (error), `IntegerArgument` (warning),
  `NoPostParameter` (error). All codes are configurable
  via standard PHPCS `<rule><type>` overrides.

### Removed

- **BREAKING:** `ImplicitPostFunction` error codes
  `DirectAccess` and `MissingPostParameter`. Use
  `MissingArgument`, `NullArgument`, or
  `IntegerArgument` instead.

## [1.1.0] - 2026-02-14

### Added

- `Apermo.PHP.ExplainCommentedOutCode` sniff: enforces
  that commented-out PHP code is preceded by a `/** */`
  doc-block with a recognized keyword (`Disabled`, `Kept`,
  `Debug`, `Review`, `WIP`).
- `Apermo.WhiteSpace.MultipleEmptyLines` sniff: no more
  than one consecutive empty line at file level, class
  level, or between functions. Auto-fixable.
- `Apermo.PHP.RequireNotInclude` sniff: forbids `include`
  and `include_once` in favor of `require`/`require_once`.
- PHPUnit test infrastructure for sniff unit tests.

### Changed

- Upgraded `PSR2.ControlStructures.ElseIfDeclaration` from
  warning to error (`else if` → `elseif`). Auto-fixable.
- Upgraded `Generic.CodeAnalysis.UnconditionalIfStatement`
  from warning to error.
- Disabled `Squiz.PHP.CommentedOutCode.Found`, superseded
  by the new `ExplainCommentedOutCode` sniff.
- Disabled `PEAR.Files.IncludingFile.UseRequire` and
  `UseRequireOnce`, superseded by `RequireNotInclude`.
- Removed downgrade of
  `Squiz.Commenting.InlineComment.InvalidEndChar` to
  warning (keeps default error).

## [1.0.0] - 2026-02-14

### Added

- Initial release with shared PHPCS ruleset.
- WordPress Coding Standards with opinionated exclusions.
- Slevomat type hint enforcement for parameters, return types, and properties.
- YoastCS integration.
- PHPCompatibility checks targeting PHP 8.3+.
- Empty `Apermo/Sniffs/` directory for future custom sniffs.

[2.2.0]: https://github.com/apermo/apermo-coding-standards/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/apermo/apermo-coding-standards/compare/v2.0.2...v2.1.0
[2.0.2]: https://github.com/apermo/apermo-coding-standards/compare/v2.0.1...v2.0.2
[2.0.1]: https://github.com/apermo/apermo-coding-standards/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/apermo/apermo-coding-standards/compare/v1.4.0...v2.0.0
[1.4.0]: https://github.com/apermo/apermo-coding-standards/compare/v1.3.1...v1.4.0
[1.3.1]: https://github.com/apermo/apermo-coding-standards/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/apermo/apermo-coding-standards/compare/v1.2.1...v1.3.0
[1.2.1]: https://github.com/apermo/apermo-coding-standards/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/apermo/apermo-coding-standards/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/apermo/apermo-coding-standards/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/apermo/apermo-coding-standards/releases/tag/v1.0.0
