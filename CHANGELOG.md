# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[1.2.0]: https://github.com/apermo/wp-coding-standards/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/apermo/wp-coding-standards/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/apermo/wp-coding-standards/releases/tag/v1.0.0
