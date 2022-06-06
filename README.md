# Rector - Instant Upgrades and Automated Refactoring

[![Downloads](https://img.shields.io/packagist/dt/rector/rector.svg?style=flat-square)](https://packagist.org/packages/rector/rector)

<br>

Rector instantly upgrades and refactors the PHP code of your application.  It can help you in 2 major areas:

### 1. Instant Upgrades

Rector now supports upgrades from PHP 5.3 to 8.1 and major open-source projects like [Symfony](https://github.com/rectorphp/rector-symfony), [PHPUnit](https://github.com/rectorphp/rector-phpunit), [Nette](https://github.com/rectorphp/rector-nette), [Laravel](https://github.com/rectorphp/rector-laravel), [CakePHP](https://github.com/rectorphp/rector-cakephp) and [Doctrine](https://github.com/rectorphp/rector-doctrine). Do you want to **be constantly on the latest PHP and Framework without effort**?

Use Rector to handle **instant upgrades** for you.

### 2. Automated Refactoring

Do you have code quality you need, but struggle to keep it with new developers in your team? Do you want to see smart code-reviews even when every senior developers sleeps?

Add Rector to your CI and let it **continuously refactor your code** and keep the code quality high.

<br>

## Read a First Book About Rector

Are you curious, how Rector works internally, how to create your own rules and test them and why Rector was born? In May 2021 we've released the very first book: *Rector - The Power of Automated Refactoring*.

<a href="https://leanpub.com/rector-the-power-of-automated-refactoring">
<img src="https://github.com/rectorphp/the-power-of-automated-refactoring-feedback/raw/main/images/book_title.png">
</a>

By [buying a book](https://leanpub.com/rector-the-power-of-automated-refactoring) you directly support maintainers who are working on Rector.

<br>

## Documentation

- [Explore 500+ Rector Rules](/docs/rector_rules_overview.md)
- [How to Ignore Rule or Paths](/docs/how_to_ignore_rule_or_paths.md)
- [Static Reflection and Autoload](/docs/static_reflection_and_autoload.md)
- [How to Configure Rule](/docs/how_to_configure_rules.md)
- [Auto Import Names](/docs/auto_import_names.md)

### For Rule Developers and Contributors

- [How to add Test for Rector Rule](/docs/how_to_add_test_for_rector_rule.md)
- [How Does Rector Work?](/docs/how_it_works.md)
- [PHP Parser Nodes](https://github.com/rectorphp/php-parser-nodes-docs/)
- [How to Work with Doc Block and Comments](/docs/how_to_work_with_doc_block_and_comments.md)
- [How to Generate New Rector Rule](/docs/create_own_rule.md)

See [the full documentation](/docs).

<br>

## Install

```bash
composer require rector/rector --dev
```

<br>

## Running Rector

There are 2 main ways to use Rector:

- a *single rule*, to have the change under control
- or group of rules called *sets*

To use them, create a `rector.php` in your root directory:

```bash
vendor/bin/rector init
```

And modify it:

```php
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    // here we can define, what sets of rules will be applied
    // tip: use "SetList" class to autocomplete sets
    $rectorConfig->sets([
        SetList::CODE_QUALITY
    ]);

    // register single rule
    $rectorConfig->rule(TypedPropertyRector::class);
};
```

Then dry run Rector:

```bash
vendor/bin/rector process src --dry-run
```

Rector will show you diff of files that it *would* change. To *make* the changes, drop `--dry-run`:

```bash
vendor/bin/rector process src
```

*Note: `rector.php` is loaded by default. For different location, use `--config` option.*

*Note: Rector will only update legacy code to utilize new features which are supported by the PHP version defined in your `composer.json` file.  For instance, if require.php is `>=7.2.5`, Rector will not make changes which are only available for PHP versions after 7.2.5.*

<br>

## Configuration

```php
// rector.php
use Rector\Core\ValueObject\PhpVersion;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    // paths to refactor; solid alternative to CLI arguments
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);

    // is your PHP version different from the one you refactor to? [default: your PHP version], uses PHP_VERSION_ID format
    $rectorConfig->phpVersion(PhpVersion::PHP_72);

    // Path to PHPStan with extensions, that PHPStan in Rector uses to determine types
    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan-for-config.neon');
};
```

<br>

## Support

Rector is a tool that [we develop](https://getrector.org/) and share for free, so anyone can automate their refactoring. But not everyone has dozens of hours to understand complexity of abstract-syntax-tree in their own time. **That's why we provide commercial support - to save your time**.

Would you like to apply Rector on your code base but don't have time for the struggle with your project? [Hire us](https://getrector.org/contact) to get there faster.

<br>

## How to Contribute

See [the contribution guide](/CONTRIBUTING.md) or go to development repository [rector/rector-src](https://github.com/rectorphp/rector-src).

<br>

## Projects using Rector

- [`codito/rector-money`](https://packagist.org/packages/codito/rector-money): set of rules related to `moneyphp/money`
  library. It can help you with upgrading to v4.0 or make your codebase compatible for future upgrade.

- [`laminas/laminas-servicemanager-migration`](https://packagist.org/packages/laminas/laminas-servicemanager-migration): set of rules related to `laminas-servicemanager`
  library. It can help migrate your code to laminas-servicemanager 4.x compatibility.

<br>

## Debugging

You can use `--debug` option, that will print nested exceptions output:

```bash
vendor/bin/rector process src/Controller --dry-run --debug
```

Or with Xdebug:

1. Make sure [Xdebug](https://xdebug.org/) is installed and configured
2. Add `--xdebug` option when running Rector

```bash
vendor/bin/rector process src/Controller --dry-run --xdebug
```

To assist with simple debugging Rector provides 2 helpers to pretty-print AST-nodes:

```php
use PhpParser\Node\Scalar\String_;

$node = new String_('hello world!');

// prints node to string, as PHP code displays it
print_node($node);

// dump nested node object with nested properties
dump_node($node);
// 2nd argument is how deep the nesting is - this makes sure the dump is short and useful
dump_node($node, 1);
```

<br>

## Known Drawbacks

### How to Apply Coding Standards?

Rector uses [nikic/php-parser](https://github.com/nikic/PHP-Parser/), built on technology called an *abstract syntax tree* (AST). An AST doesn't know about spaces and when written to a file it produces poorly formatted code in both PHP and docblock annotations. **That's why your project needs to have a coding standard tool** and a set of formatting rules, so it can make Rector's output code nice and shiny again.

We're using [ECS](https://github.com/symplify/easy-coding-standard) with [this setup](https://github.com/rectorphp/rector-src/blob/main/ecs.php).
