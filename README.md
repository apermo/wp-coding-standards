# Apermo WordPress Coding Standards

[![CI](https://github.com/apermo/apermo-coding-standards/actions/workflows/ci.yml/badge.svg)](https://github.com/apermo/apermo-coding-standards/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/apermo/apermo-coding-standards/graph/badge.svg)](https://codecov.io/gh/apermo/apermo-coding-standards)
[![Packagist Version](https://img.shields.io/packagist/v/apermo/apermo-coding-standards)](https://packagist.org/packages/apermo/apermo-coding-standards)
[![PHP Version](https://img.shields.io/packagist/dependency-v/apermo/apermo-coding-standards/php)](https://packagist.org/packages/apermo/apermo-coding-standards)
[![License](https://img.shields.io/packagist/l/apermo/apermo-coding-standards)](LICENSE)

Shared [PHPCS](https://github.com/PHPCSStandards/PHP_CodeSniffer) ruleset for WordPress projects. Combines WordPress Coding Standards, Slevomat type hints, YoastCS, and PHPCompatibility into a single reusable standard.

## Requirements

- PHP 7.4+

## Installation

```bash
composer require --dev apermo/apermo-coding-standards
```

The [Composer Installer Plugin](https://github.com/PHPCSStandards/composer-installer) automatically registers the standard with PHPCS.

## Usage

Reference the `Apermo` standard in your project's `phpcs.xml`:

```xml
<?xml version="1.0"?>
<ruleset name="My Project">
    <file>.</file>
    <arg name="extensions" value="php"/>
    <arg value="-colors"/>
    <arg value="ns"/>

    <rule ref="Apermo"/>
</ruleset>
```

Then run:

```bash
vendor/bin/phpcs
```

## What's Included

| Standard | Purpose |
|---|---|
| [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards) | WordPress PHP conventions |
| [Slevomat Coding Standard](https://github.com/slevomat/coding-standard) | Type hint enforcement |
| [YoastCS](https://github.com/Yoast/yoastcs) | Additional quality rules |
| [PHPCompatibility](https://github.com/PHPCompatibility/PHPCompatibilityWP) | PHP version compatibility checks |

### Notable Opinions

- Short array syntax (`[]`) enforced, long array syntax (`array()`) forbidden
- Short ternary operators allowed
- Yoda conditions disallowed
- Short open echo tags (`<?=`) allowed
- Type hints enforced for parameters, return types, and properties
- Closures limited to 5 lines
- Use statements must be alphabetically sorted
- Unused imports are flagged
- No more than 1 consecutive empty line (file-level, class-level, between functions)
- `require`/`require_once` enforced over `include`/`include_once`
- `elseif` enforced over `else if`
- Unconditional `if` statements (`if (true)`) are errors
- `stdClass` usage discouraged — `new \stdClass()` and `(object)` casts warned
- Hook invocations (`do_action`, `apply_filters`) require PHPDoc blocks
- Assignment alignment must be consistent within groups (all aligned or all single-space)
- Nested closures and arrow functions are warned

### Forbidden Nested Closures (`Apermo.Functions.ForbiddenNestedClosure`)

Closures and arrow functions nested inside other closures or arrow functions are warned. Extract the inner callback to a named function instead.

```php
// Bad — nested closures
$fn = function () {
    $inner = function () {
        return 1;
    };
};

// Bad — nested arrow functions
$fn = fn() => fn() => 1;

// Good — extract to named function
function get_one(): int {
    return 1;
}
$fn = function () {
    $inner = get_one();
};
```

**Customization** via `phpcs.xml`:

```xml
<!-- Disable entirely -->
<rule ref="Apermo.Functions.ForbiddenNestedClosure.NestedClosure">
    <severity>0</severity>
</rule>
```

### Commented-Out Code (`Apermo.PHP.ExplainCommentedOutCode`)

Commented-out PHP code in `//` comments must be preceded by a `/** */` doc-block explanation starting with a recognized keyword:

| Keyword | Intent |
|---|---|
| `Disabled` | Temporarily turned off, will be re-enabled |
| `Kept` | Intentionally preserved as reference or rollback |
| `Debug` | Diagnostic code kept for future troubleshooting |
| `Review` | Seen but needs human review before deciding |
| `WIP` | Work in progress, actively being developed |

**Examples:**

```php
/** Disabled: Plugin doesn't support PHP 8.3 yet. */
// add_action( 'init', 'my_func' );

/** Review (2026-02-14): Found during refactor, unclear if still needed. */
// register_post_type( 'legacy_type', $args );
```

An optional date in `YYYY-MM-DD` format can be added in parentheses after the keyword.

Supersedes `Squiz.PHP.CommentedOutCode` — that rule is disabled automatically.

**Customization** via `phpcs.xml`:

```xml
<!-- Add custom keywords -->
<rule ref="Apermo.PHP.ExplainCommentedOutCode">
    <properties>
        <property name="keywords" value="Disabled,Kept,Debug,Review,WIP,Deprecated"/>
    </properties>
</rule>

<!-- Downgrade to warning instead of error -->
<rule ref="Apermo.PHP.ExplainCommentedOutCode">
    <properties>
        <property name="error" value="false"/>
    </properties>
</rule>
```

### Multiple Empty Lines (`Apermo.WhiteSpace.MultipleEmptyLines`)

No more than one consecutive empty line is allowed outside functions and closures. Inside functions, `Squiz.WhiteSpace.SuperfluousWhitespace` already enforces this.

Auto-fixable with `phpcbf`.

```php
// Bad — 2+ consecutive empty lines at file/class level
$a = 1;


$b = 2;

// Good — at most 1 empty line
$a = 1;

$b = 2;
```

**Customization** via `phpcs.xml`:

```xml
<!-- Downgrade to warning -->
<rule ref="Apermo.WhiteSpace.MultipleEmptyLines">
    <type>warning</type>
</rule>

<!-- Disable entirely -->
<rule ref="Apermo.WhiteSpace.MultipleEmptyLines.MultipleEmptyLines">
    <severity>0</severity>
</rule>
```

### Require Not Include (`Apermo.PHP.RequireNotInclude`)

`include` and `include_once` are forbidden because they silently continue on failure. Use `require`/`require_once` instead.

Not auto-fixable (changing include to require may alter behavior).

```php
// Bad
include 'file.php';
include_once 'helpers.php';

// Good
require 'file.php';
require_once 'helpers.php';
```

Use `// phpcs:ignore Apermo.PHP.RequireNotInclude` to suppress when `include` is genuinely intended.

Separate error codes (`IncludeFound`, `IncludeOnceFound`) allow independent configuration:

```xml
<!-- Allow include but not include_once -->
<rule ref="Apermo.PHP.RequireNotInclude.IncludeFound">
    <severity>0</severity>
</rule>
```

### Array Complexity (`Apermo.DataStructures.ArrayComplexity`)

Flags deeply nested or wide associative arrays that would benefit from typed objects (DTOs, value objects). Arrays with many string keys or deep nesting often indicate data structures that should be classes.

Two independent checks, each with a warning and error threshold:

| Check | Warning | Error | Default |
|---|---|---|---|
| Nesting depth | `TooDeep` | `TooDeepError` | warn > 2, error > 3 |
| Key count | `TooManyKeys` | `TooManyKeysError` | warn > 5, error > 10 |

Only outermost arrays are checked — nested sub-arrays are not reported separately. Numeric arrays (without `=>`) are ignored entirely.

```php
// Warning — 3 levels of associative nesting
$order = [
    'customer' => [
        'address' => [
            'city' => 'Berlin',
        ],
    ],
];

// Warning — 6 associative keys
$user = [
    'id'       => 1,
    'name'     => 'John',
    'email'    => 'john@example.com',
    'role'     => 'admin',
    'active'   => true,
    'verified' => true,
];

// OK — numeric arrays are ignored
$grid = [ [ 1, 2 ], [ 3, 4 ] ];
```

**Customization** via `phpcs.xml`:

```xml
<!-- Adjust thresholds -->
<rule ref="Apermo.DataStructures.ArrayComplexity">
    <properties>
        <property name="warnDepth" value="3"/>
        <property name="errorDepth" value="5"/>
        <property name="warnKeys" value="8"/>
        <property name="errorKeys" value="15"/>
    </properties>
</rule>

<!-- Disable key count checks entirely -->
<rule ref="Apermo.DataStructures.ArrayComplexity.TooManyKeys">
    <severity>0</severity>
</rule>
<rule ref="Apermo.DataStructures.ArrayComplexity.TooManyKeysError">
    <severity>0</severity>
</rule>
```

### Global Post Access (`Apermo.WordPress.GlobalPostAccess`)

Flags `global $post;` inside functions, methods, closures, and arrow functions. Top-level (template) usage is allowed because the WordPress loop sets `$post` there. Functions should receive `WP_Post` or a post ID as a parameter.

```php
// Bad — hidden dependency on global state
function get_title() {
    global $post;
    return $post->post_title;
}

// Good — explicit dependency
function get_title( WP_Post $post ) {
    return $post->post_title;
}
```

### Implicit Post Function (`Apermo.WordPress.ImplicitPostFunction`)

Flags WordPress template functions called without an explicit post argument inside function scopes. These functions implicitly read the global `$post`, creating hidden dependencies.

Severity depends on what was passed, not which function:

| Code | Severity | When |
|---|---|---|
| `MissingArgument` | error | Post param exists but no argument provided |
| `NullArgument` | error | Literal `null` passed as post argument |
| `IntegerArgument` | warning | Literal int or `$var->ID` passed |
| `NoPostParameter` | error | Function has no post param at all |

```php
// Bad — implicit global access inside function
function render() {
    $title = get_the_title();        // error: MissingArgument
    $id    = get_the_ID();           // error: NoPostParameter
    get_the_title( null );           // error: NullArgument
    get_the_title( $post->ID );      // warning: IntegerArgument
}

// Good — explicit post argument
function render( WP_Post $post ) {
    $title = get_the_title( $post );
    $id    = $post->ID;
}
```

**Customization** via `phpcs.xml`:

```xml
<!-- Downgrade to warning during migration -->
<rule ref="Apermo.WordPress.ImplicitPostFunction.MissingArgument">
    <type>warning</type>
</rule>

<!-- Disable NoPostParameter errors entirely -->
<rule ref="Apermo.WordPress.ImplicitPostFunction.NoPostParameter">
    <severity>0</severity>
</rule>
```

### Forbidden stdClass (`Apermo.PHP.ForbiddenObjectCast` + `SlevomatCodingStandard.PHP.ForbiddenClasses`)

Discourages `stdClass` usage in favor of typed classes. Two rules work together:

- `Apermo.PHP.ForbiddenObjectCast` warns on `(object)` casts
- `SlevomatCodingStandard.PHP.ForbiddenClasses` warns on `new \stdClass()`

Both emit warnings (not errors) to allow gradual migration.

```php
// Bad — untyped data bags
$config = (object) [ 'host' => 'localhost', 'port' => 3306 ];
$dto = new \stdClass();

// Good — typed classes
class DbConfig {
    public function __construct(
        public string $host,
        public int $port,
    ) {}
}
$config = new DbConfig( 'localhost', 3306 );
```

**Customization** via `phpcs.xml`:

```xml
<!-- Disable the (object) cast warning -->
<rule ref="Apermo.PHP.ForbiddenObjectCast.Found">
    <severity>0</severity>
</rule>

<!-- Disable the new stdClass() warning -->
<rule ref="SlevomatCodingStandard.PHP.ForbiddenClasses">
    <severity>0</severity>
</rule>
```

### Hook Documentation (`Apermo.Hooks.RequireHookDocBlock`)

WordPress hook invocations (`do_action`, `apply_filters`, and their `_ref_array` and `_deprecated` variants) must be preceded by a PHPDoc block.

The sniff checks:

| Code | When |
|---|---|
| `Missing` | No PHPDoc block before the hook call |
| `MissingParam` | Hook passes arguments but doc block has no `@param` tags |
| `MissingReturn` | `apply_filters*` call without a `@return` tag |

All violations are errors.

```php
// Bad — no documentation
do_action( 'my_plugin_init', $config );

// Good — documented hook
/**
 * Fires after plugin initialization.
 *
 * @param array $config Plugin configuration.
 */
do_action( 'my_plugin_init', $config );

// Bad — filter missing @return
/**
 * @param string $title The title.
 */
apply_filters( 'my_title', $title );

// Good — filter with @return
/**
 * Filters the display title.
 *
 * @param string $title The title.
 *
 * @return string Filtered title.
 */
apply_filters( 'my_title', $title );
```

**Customization** via `phpcs.xml`:

```xml
<!-- Disable entirely -->
<rule ref="Apermo.Hooks.RequireHookDocBlock">
    <severity>0</severity>
</rule>

<!-- Only require doc blocks, skip param/return checks -->
<rule ref="Apermo.Hooks.RequireHookDocBlock.MissingParam">
    <severity>0</severity>
</rule>
<rule ref="Apermo.Hooks.RequireHookDocBlock.MissingReturn">
    <severity>0</severity>
</rule>
```

### Consistent Assignment Alignment (`Apermo.Formatting.ConsistentAssignmentAlignment`)

Consecutive assignment statements must use a consistent style: either all `=` operators are aligned to the same column, or all use a single space before `=`. Mixing styles within a group is warned. Auto-fixable with `phpcbf` — deviators are adjusted to match the majority style.

Groups where all operators are aligned but padded beyond the longest variable + 1 space are flagged as `OverAligned` errors (not auto-fixable).

A group of assignments breaks on: blank lines, non-assignment statements, or EOF.

Supersedes `Generic.Formatting.MultipleStatementAlignment` — that rule is disabled automatically.

```php
// OK — all single-space
$a = 1;
$bb = 2;
$ccc = 3;

// OK — all aligned
$a   = 1;
$bb  = 2;
$ccc = 3;

// Warning (fixable) — mixed styles
$short = 1;
$veryLongName = 2;
$x            = 3;

// Error — over-aligned
$short     = 1;
$medium    = 2;
$long      = 3;
```

**Customization** via `phpcs.xml`:

```xml
<!-- Disable inconsistency warnings -->
<rule ref="Apermo.Formatting.ConsistentAssignmentAlignment.InconsistentAlignment">
    <severity>0</severity>
</rule>

<!-- Disable over-alignment errors -->
<rule ref="Apermo.Formatting.ConsistentAssignmentAlignment.OverAligned">
    <severity>0</severity>
</rule>
```

### Consistent Double Arrow Alignment (`Apermo.Arrays.ConsistentDoubleArrowAlignment`)

Multi-line associative arrays must use a consistent `=>` style: either all arrows are aligned to the same column, or all use a single space before `=>`. Mixing styles within an array is warned. Auto-fixable with `phpcbf` — deviators are adjusted to match the majority style.

Arrays where all arrows are aligned but padded beyond the longest key + 1 space are flagged as `OverAligned` errors (not auto-fixable).

Only outermost arrays are checked — nested sub-arrays are analyzed independently. Single-line arrays are skipped.

Supersedes `WordPress.Arrays.MultipleStatementAlignment` — that rule is disabled automatically.

```php
// OK — all single-space
$config = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'mydb',
];

// OK — all aligned
$config = [
    'host'     => 'localhost',
    'port'     => 3306,
    'database' => 'mydb',
];

// Warning (fixable) — mixed styles
$config = [
    'host' => 'localhost',
    'port' => 3306,
    'database_name' => 'mydb',
    'x'             => 'value',
];

// Error — over-aligned
$config = [
    'a'      => 1,
    'bb'     => 2,
    'ccc'    => 3,
];
```

**Customization** via `phpcs.xml`:

```xml
<!-- Disable inconsistency warnings -->
<rule ref="Apermo.Arrays.ConsistentDoubleArrowAlignment.InconsistentAlignment">
    <severity>0</severity>
</rule>

<!-- Disable over-alignment errors -->
<rule ref="Apermo.Arrays.ConsistentDoubleArrowAlignment.OverAligned">
    <severity>0</severity>
</rule>
```

### Elseif Over Else If (`PSR2.ControlStructures.ElseIfDeclaration`)

`else if` must be written as `elseif`. Upgraded from the PSR2 default warning to an error.

Auto-fixable with `phpcbf`.

```php
// Bad
if ( $a ) {
    // ...
} else if ( $b ) {
    // ...
}

// Good
if ( $a ) {
    // ...
} elseif ( $b ) {
    // ...
}
```

## Custom Sniffs

Place custom sniffs in `Apermo/Sniffs/<Category>/<SniffName>Sniff.php`. PHPCS discovers them automatically.

Example: `Apermo/Sniffs/Naming/FunctionPrefixSniff.php` is referenced as `Apermo.Naming.FunctionPrefix`.

## Contributing

### Development

```bash
composer install       # Install dependencies
composer test          # Run PHPUnit tests
composer analyse       # Run PHPStan static analysis
```

### Release Process

1. Create a `release/X.Y.Z` branch from `main`
2. Update `CHANGELOG.md` with the version heading and release date
3. Open a PR — CI runs tests, PHPStan, and validates the changelog
4. Merge the PR — GitHub Actions creates a draft release with the tag
5. Review and publish the draft release on GitHub

## Disclaimer

This project is developed with major assistance from AI tooling. Projects with stricter rules regarding the use of AI-generated code should refrain from forking or reusing code from this repository.

## License

[MIT](LICENSE)
