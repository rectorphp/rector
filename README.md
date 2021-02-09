# Rector - Speedup Your PHP Development

[![Coverage Status](https://img.shields.io/coveralls/rectorphp/rector/master.svg?style=flat-square)](https://coveralls.io/github/rectorphp/rector?branch=master)
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

[Hire us](https://getrector.org/contact) to skip learning Rector, AST and nodes, to educate your team about Rectors benefits and to setup Rector in your project, so that you can enjoy the 300 % development speed :+1:

<br>

## Open-Source First

Rector **instantly upgrades and refactors the PHP code of your application**.
It supports all versions of PHP from 5.3 and major open-source projects:

<br>

<p align="center">
    <img src="/docs/images/php.png">
    <img src="/docs/images/space.png" width=40>
    <img src="/docs/images/symfony.png">
    <img src="/docs/images/space.png" width=40>
    <a href="https://github.com/palantirnet/drupal-rector">
        <img src="/docs/images/drupal.png" alt="Drupal Rector rules">
    </a>
    <br>
    <img src="/docs/images/space.png" height=15>
    <br>
    <img src="/docs/images/cakephp.png">
    <img src="/docs/images/space.png" width=40>
    <a href="https://github.com/sabbelasichon/typo3-rector">
        <img src="/docs/images/typo3.png">
    </a>
    <br>
    <img src="/docs/images/space.png" height=15>
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

- [Explore 660+ Rector Rules](/docs/rector_rules_overview.md)
- [How Does Rector Work?](/docs/how_it_works.md)
- [PHP Parser Nodes](https://github.com/rectorphp/php-parser-nodes-docs/blob/master/docs/nodes_overview.md)

### Advanced

- [How to Ignore Rule or Paths](/docs/how_to_ignore_rule_or_paths.md)
- [How to Configure Rule](/docs/how_to_configure_rules.md)
- [How to Run Rector on Changed Files Only](/docs/how_to_run_rector_on_changed_files_only.md)

### Contributing

- [How to add Test for Rector Rule](/docs/how_to_add_test_for_rector_rule.md)
- [How to work with Doc Block and Comments](/docs/how_to_work_with_doc_block_and_comments.md)
- [How to Create New Rector Rule](/docs/create_own_rule.md)

<br>

## Install

```bash
composer require rector/rector --dev
```

- Having conflicts during `composer require`? → Use the [Rector Prefixed](https://github.com/rectorphp/rector-prefixed)
- Using a different PHP version than Rector supports? → Use the [Docker image](/docs/how_to_run_rector_in_docker.md)

<br>

## Running Rector

There a 2 main ways to use Rector:

- a *single rule*, to have the change under control - you can choose [from over 600 rules](/docs/rector_rules_overview.md)
- or group of rules called *sets* - [pick from sets](/config/set)

To use them, create a `rector.php` in your root directory:

```bash
vendor/bin/rector init
```

And modify it:

```php
// rector.php


use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // here we can define, what sets of rules will be applied
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SETS, [SetList::CODE_QUALITY]);

    // register single rule
    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class);
};
```

<br>

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

## Full Config Configuration

```php
// rector.php
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // paths to refactor; solid alternative to CLI arguments
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);

    // Rector relies on autoload setup of your project; Composer autoload is included by default; to add more:
    $parameters->set(Option::AUTOLOAD_PATHS, [
        // autoload specific file
        __DIR__ . '/vendor/squizlabs/php_codesniffer/autoload.php',
        // or full directory
        __DIR__ . '/vendor/project-without-composer',
    ]);

    // is your PHP version different from the one your refactor to? [default: your PHP version], uses PHP_VERSION_ID format
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_72);

    // auto import fully qualified class names? [default: false]
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    // skip root namespace classes, like \DateTime or \Exception [default: true]
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);

    // skip classes used in PHP DocBlocks, like in /** @var \Some\Class */ [default: true]
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);

    // Run Rector only on changed files
    $parameters->set(Option::ENABLE_CACHE, true);

    // Path to phpstan with extensions, that PHPSTan in Rector uses to determine types
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan-for-config.neon');
};
```

### Symfony Container

To work with some Symfony rules, you now need to link your container XML file

```php
// rector.php


use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
        __DIR__ . '/var/cache/dev/AppKernelDevDebugContainer.xml'
    );
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

To assist with echo-style debugging rector provides a `rd()` helper method which is useful to pretty-print AST-nodes:

```php
/**
 * @param Class_ $node
 */
public function refactor(Node $node): ?Node
{
    rd($node);
    die;
}
```

<br>

## Community Packages

Do you use Rector to upgrade your code? Add it here:

- [palantirnet/drupal-rector](https://github.com/palantirnet/drupal-rector) by [Palantir.net](https://github.com/palantirnet) for [Drupal](https://www.drupal.org/)
- [sabbelasichon/typo3-rector](https://github.com/sabbelasichon/typo3-rector) for [TYPO3](https://typo3.org/)

## Known Drawbacks

### How to Apply Coding Standards?

Rector uses [nikic/php-parser](https://github.com/nikic/PHP-Parser/), built on technology called an *abstract syntax tree* (AST). An AST doesn't know about spaces and when written to a file it produces poorly formatted code in both PHP and docblock annotations. **That's why your project needs to have a coding standard tool** and a set of formatting rules, so it can make Rector's output code nice and shiny again.

We're using [ECS](https://github.com/symplify/easy-coding-standard) with [this setup](ecs.php).
