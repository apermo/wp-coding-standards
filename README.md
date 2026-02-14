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

## Custom Sniffs

Place custom sniffs in `Apermo/Sniffs/<Category>/<SniffName>Sniff.php`. PHPCS discovers them automatically.

Example: `Apermo/Sniffs/Naming/FunctionPrefixSniff.php` is referenced as `Apermo.Naming.FunctionPrefix`.

## License

[MIT](LICENSE)
