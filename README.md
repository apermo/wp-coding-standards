# Apermo WordPress Coding Standards

Shared [PHPCS](https://github.com/PHPCSStandards/PHP_CodeSniffer) ruleset for WordPress projects. Combines WordPress Coding Standards, Slevomat type hints, YoastCS, and PHPCompatibility into a single reusable standard.

## Requirements

- PHP 8.3+

## Installation

```bash
composer require --dev apermo/wp-coding-standards
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

Three error codes at different severities:

| Code | Severity | Applies to |
|---|---|---|
| `DirectAccess` | error | `get_post()`, `get_the_ID()`, `the_ID()` |
| `MissingPostParameter` | warning | Functions with optional post param not provided |
| `NoPostParameter` | warning | Functions that have no post param at all |

```php
// Bad — implicit global access inside function
function render() {
    $title = get_the_title();      // warning
    $id    = get_the_ID();         // error
}

// Good — explicit post argument
function render( WP_Post $post ) {
    $title = get_the_title( $post );
    $id    = $post->ID;
}
```

**Customization** via `phpcs.xml`:

```xml
<!-- Downgrade DirectAccess errors to warnings -->
<rule ref="Apermo.WordPress.ImplicitPostFunction.DirectAccess">
    <type>warning</type>
</rule>

<!-- Disable NoPostParameter warnings entirely -->
<rule ref="Apermo.WordPress.ImplicitPostFunction.NoPostParameter">
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

## License

[MIT](LICENSE)
