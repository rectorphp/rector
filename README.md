# Rector - Instant Upgrades and Automated Refactoring

[![Downloads](https://img.shields.io/packagist/dt/rector/rector.svg?style=flat-square)](https://packagist.org/packages/rector/rector)

<br>

Rector helps you with 2 areas - major code changes and in daily work.

- Do you have a legacy code base? Do you want to have that latest version of PHP or your favorite framework?
  → **Rector gets you there with instant upgrade**.

<br>

- Do you have code quality you need, but struggle to keep it with new developers in your team? Do you wish to have  code-reviews for each member of your team, but don't have time for it?
  → **Add Rector to you CI and let it fix your code for you. Get [instant feedback](https://tomasvotruba.com/blog/2020/01/13/why-is-first-instant-feedback-crucial-to-developers/) after each commit.**

<br>

It's a tool that [we develop](https://getrector.org/) and share for free, so anyone can automate their refactoring.

[Hire us](https://getrector.org/contact) to speed up learning Rector, AST and nodes, to educate your team about Rectors benefits and to setup Rector in your project, so that you can enjoy the 300 % development speed :+1:

<br>

## Open-Source First

Rector **instantly upgrades and refactors the PHP code of your application**.
It supports all versions of PHP from 5.3 and major open-source projects:

<br>

<p align="center">
    <img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/php.png">
    <img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/space.png" width=30>
    <a href="https://github.com/rectorphp/rector-phpunit"><img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/phpunit.png"></a>
    <img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/space.png" width=30>
    <a href="https://github.com/rectorphp/rector-symfony"><img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/symfony.png"></a>
    <img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/space.png" width=30>
    <a href="https://github.com/palantirnet/drupal-rector">
        <img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/drupal.png" alt="Drupal Rector rules">
    </a>
    <br>
    <img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/space.png" height=15>
    <br>
    <a href="https://github.com/rectorphp/rector-cakephp"><img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/cakephp.png"></a>
    <img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/space.png" width=30>
    <a href="https://github.com/sabbelasichon/typo3-rector">
        <img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/typo3.png">
    </a>
    <br>
    <img src="https://github.com/rectorphp/rector-src/blob/main/docs/images/space.png" height=15>
</p>

### What Can Rector Do for You?

- [Complete 2800 `@var` types in 2 minutes](https://tomasvotruba.com/blog/2019/07/29/how-we-completed-thousands-of-missing-var-annotations-in-a-day/)
- [Upgrade 30 000 unit tests from PHPUnit 6 to 9](https://twitter.com/LBajsarowicz/status/1272947900016967683)
- [Complete PHP 7.4 Property Types](https://tomasvotruba.com/blog/2018/11/15/how-to-get-php-74-typed-properties-to-your-code-in-few-seconds/)
- [Migrate from Nette to Symfony](https://tomasvotruba.com/blog/2019/02/21/how-we-migrated-from-nette-to-symfony-in-3-weeks-part-1/)
- [Refactor Laravel Facades to Dependency Injection](https://tomasvotruba.com/blog/2019/03/04/how-to-turn-laravel-from-static-to-dependency-injection-in-one-day/)
- And much more...

<br>

## Documentation

- [Explore 450+ Rector Rules](https://github.com/rectorphp/rector-src/blob/main/docs/rector_rules_overview.md)
- [How Does Rector Work?](https://github.com/rectorphp/rector-src/blob/main/docs/how_it_works.md)
- [PHP Parser Nodes](https://github.com/rectorphp/php-parser-nodes-docs/)

### Advanced

- [Auto Import Names](https://github.com/rectorphp/rector-src/blob/main/docs/auto_import_names.md)
- [How to Ignore Rule or Paths](https://github.com/rectorphp/rector-src/blob/main/docs/how_to_ignore_rule_or_paths.md)
- [Static Reflection and Autoload](https://github.com/rectorphp/rector-src/blob/main/docs/static_reflection_and_autoload.md)
- [How to Configure Rule](https://github.com/rectorphp/rector-src/blob/main/docs/how_to_configure_rules.md)
- [How to Generate Configuration file](https://github.com/rectorphp/rector-src/blob/main/docs/init_command.md)

### Contributing

- [How to add Test for Rector Rule](https://github.com/rectorphp/rector-src/blob/main/docs/how_to_add_test_for_rector_rule.md)
- [How to work with Doc Block and Comments](https://github.com/rectorphp/rector-src/blob/main/docs/how_to_work_with_doc_block_and_comments.md)
- [How to Create New Rector Rule](https://github.com/rectorphp/rector-src/blob/main/docs/create_own_rule.md)

<br>

## Install

```bash
composer require rector/rector --dev
```

- Having conflicts during `composer require`? → Use the [Rector Prefixed](https://github.com/rectorphp/rector-prefixed) with PHP 7.1+ version
- Using a different PHP version than Rector supports? → Use the [Docker image](https://github.com/rectorphp/rector-src/blob/main/docs/how_to_run_rector_in_docker.md)

<br>

## Running Rector

There a 2 main ways to use Rector:

- a *single rule*, to have the change under control
- or group of rules called *sets*

To use them, create a `rector.php` in your root directory:

```bash
vendor/bin/rector init
```

And modify it:

```php
// rector.php
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // here we can define, what sets of rules will be applied
    // tip: use "SetList" class to autocomplete sets
    $containerConfigurator->import(SetList::CODE_QUALITY);

    // register single rule
    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class);
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

<br>

## Configuration

```php
// rector.php
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // paths to refactor; solid alternative to CLI arguments
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);

    // is your PHP version different from the one your refactor to? [default: your PHP version], uses PHP_VERSION_ID format
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_72);

    // Run Rector only on changed files
    $parameters->set(Option::ENABLE_CACHE, true);

    // Path to phpstan with extensions, that PHPSTan in Rector uses to determine types
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan-for-config.neon');
};
```

<br>

## How to Contribute

See [the contribution guide](/CONTRIBUTING.md).

<br>

### Debugging

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

To assist with simple debugging Rector provides a 2 helpers to pretty-print AST-nodes:

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

We're using [ECS](https://github.com/symplify/easy-coding-standard) with [this setup](ecs.php).
