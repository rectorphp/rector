# Rector - Upgrade Your Legacy App to a Modern Codebase

Rector is a **rec**onstruc**tor** tool - it does **instant upgrades** and **instant refactoring** of your code.
Why refactor manually if Rector can handle 80% of the task for you?

[![Coverage Status](https://img.shields.io/coveralls/rectorphp/rector/master.svg?style=flat-square)](https://coveralls.io/github/rectorphp/rector?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/rector/rector.svg?style=flat-square)](https://packagist.org/packages/rector/rector)
[![SonarCube](https://img.shields.io/badge/SonarCube_Debt-%3C27-brightgreen.svg?style=flat-square)](https://sonarcloud.io/dashboard?id=rectorphp_rector)

<br>

- **[Online DEMO](https://getrector.org/demo)**
- [Explore 500+ Rector Rules](/docs/rector_rules_overview.md)

---

![Rector-showcase](docs/images/rector-showcase-var.gif)

<br>

## Sponsors

Rector grows faster with your help, the more you help the more work it saves you.
Check out [Rector's Patreon](https://www.patreon.com/rectorphp). One-time donations are welcome [through PayPal](https://www.paypal.me/rectorphp).

Thank you:

<p>
    <a href="https://www.startupjobs.cz/en/startup/scrumworks-s-r-o"><img src="/docs/images/amateri.png"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="https://zenika.ca/en/en"><img src="/docs/images/zenika.png"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="https://spaceflow.io/en"><img src="/docs/images/spaceflow.png"></a>
</p>

<br>

## Open-Source First

Rector **instantly upgrades and instantly refactors the PHP code of your application**.

It supports all versions of PHP from 5.2 and many open-source projects:

<br>

<p align="center">
    <a href="/config/set/php"><img src="/docs/images/php.png"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="/config/set/symfony"><img src="/docs/images/symfony.png"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="/config/set/laravel"><img src="/docs/images/laravel.png"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="/config/set/twig"><img src="/docs/images/twig.png"></a>
    <br>
    <a href="https://github.com/palantirnet/drupal-rector/tree/master/config/drupal-8"><img src="/docs/images/drupal.png" alt="Drupal Rector rules"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="/config/set/cakephp"><img src="/docs/images/cakephp.png"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="/config/set/phpunit"><img src="/docs/images/phpunit.png"></a>
</p>

<br>

## What Can Rector Do for You?

- [Upgrade 30 000 unit tests from PHPUnit 6 to 9 in 2 weeks](https://twitter.com/LBajsarowicz/status/1272947900016967683)
- Complete [@var annotations or parameter/return type declarations](https://tomasvotruba.com/blog/2019/01/03/how-to-complete-type-declarations-without-docblocks-with-rector/)
- [Complete PHP 7.4 property type declarations](https://tomasvotruba.com/blog/2018/11/15/how-to-get-php-74-typed-properties-to-your-code-in-few-seconds/)
- Upgrade your code from **PHP 5.3 to 8.0**
- [Migrate your project from Nette to Symfony](https://tomasvotruba.com/blog/2019/02/21/how-we-migrated-from-nette-to-symfony-in-3-weeks-part-1/)
- [Refactor Laravel facades to dependency injection](https://tomasvotruba.com/blog/2019/03/04/how-to-turn-laravel-from-static-to-dependency-injection-in-one-day/)
- And much more...

<br>

## Install

```bash
composer require rector/rector --dev
```

- Having conflicts during `composer require`? → Use the [Rector Prefixed](https://github.com/rectorphp/rector-prefixed)
- Using a different PHP version than Rector supports? → Use the [Docker image](#run-rector-in-docker)

<br>

## Running Rector

There a 2 main ways to use Rector:

- a *single rule*, to have the change under control - pick [from over 550 rules](/docs/rector_rules_overview.md)
- or group of rules called *sets* - pick from [sets](/config/set)

Sets are suitable for open-source projects and design patterns, like .

To use them, create a `rector.php` in your root directory:

```php
<?php

// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();

    // here we can define, what sets of rules will be applied
    $parameters->set(Option::SETS, [SetList::CODE_QUALITY]);

    // get services
    $services = $containerConfigurator->services();

    // register single rule
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

<br>

*Note: `rector.php` is loaded by default. For different location, use `--config` option.*

<br>

## Configuration

```php
<?php

// rector.php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // paths to refactor; solid alternative to CLI arguments
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);

    // is there a file you need to skip?
    $parameters->set(Option::EXCLUDE_PATHS, [
        // single file
        __DIR__ . '/src/ComplicatedFile.php',
        // or directory
        __DIR__ . '/src',
        // or fnmatch
        __DIR__ . '/src/*/Tests/*',
    ]);

    // Rector relies on autoload setup of your project; Composer autoload is included by default; to add more:
    $parameters->set(Option::AUTOLOAD_PATHS, [
        // autoload specific file
        __DIR__ . '/vendor/squizlabs/php_codesniffer/autoload.php',
        // or full directory
        __DIR__ . '/vendor/project-without-composer',
    ]);

    // is there single rule you don't like from a set you use?
    $parameters->set(Option::EXCLUDE_RECTORS, [SimplifyIfReturnBoolRector::class]);

    // is your PHP version different from the one your refactor to? [default: your PHP version]
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.2');

    // auto import fully qualified class names? [default: false]
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    // skip root namespace classes, like \DateTime or \Exception [default: true]
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);
    // skip classes used in PHP DocBlocks, like in /** @var \Some\Class */ [default: true]
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);

    // skip directory/file by rule
    $parameters->set(Option::SKIP, [
        Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector::class => [
            // single file
            __DIR__ . '/src/ComplicatedFile.php',
            // or directory
            __DIR__ . '/src',
            // or fnmatch
            __DIR__ . '/src/*/Tests/*',
        ],
        Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector::class => [
            // single file
            __DIR__ . '/src/ComplicatedFile.php',
            // or directory
            __DIR__ . '/src',
            // or fnmatch
            __DIR__ . '/src/*/Tests/*',
        ],
    ]);
};
```

### Configuring Rectors

Every rector can have its own configuration. E.g. the `DowngradeTypedPropertyRector` rule will add a docblock or not depending on its property `ADD_DOC_BLOCK`:

```php
<?php

// rector.php

declare(strict_types=1);

use Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Set configuration
    // ...

    // get services
    $services = $containerConfigurator->services();

    // Don't output the docBlocks when removing typed properties
    $services->set(DowngradeTypedPropertyRector::class)
        ->call('configure', [[
            DowngradeTypedPropertyRector::ADD_DOC_BLOCK => false,
        ]]);
};
```

### Ignore Rector Rule in File

For in-file exclusion, use `@noRector \FQN name` annotation:

```php
<?php

declare(strict_types=1);

class SomeClass
{
    /**
     * @noRector
     */
    public const NAME = '102';

    /**
     * @noRector
     */
    public function foo(): void
    {
        /** @noRector \Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector */
        round(1 + 0);
    }
}
```

### Run Just 1 Rector Rule

Do you have config that includes many sets and Rectors? You might want to run only a single Rector. The `--only` argument allows that, e.g.:

```bash
vendor/bin/rector process src --set solid --only Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector

# or just a short class name
vendor/bin/rector process src --set solid --only FinalizeClassesWithoutChildrenRector
```

### Limit Execution to Changed Files

Execution can be limited to changed files using the `process` option `--match-git-diff`.
This option will filter the files included by the configuration, creating an intersection with the files listed in `git diff`.

```bash
vendor/bin/rector process src --match-git-diff
```

This option is useful in CI with pull-requests that only change few files.

### Symfony Container

To work with some Symfony rules, you now need to link your container XML file

```php
<?php

// rector.php

declare(strict_types=1);

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

## More Detailed Documentation

- **[All Rectors Overview](/docs/rector_rules_overview.md)**
- [Create own Rule](/docs/create_own_rule.md)
- [Generate Rector from Recipe](/docs/rector_recipe.md)
- [How Does Rector Work?](/docs/how_it_works.md)
- [PHP Parser Nodes Overview](/docs/nodes_overview.md)
- [Add Checkstyle with your CI](/docs/checkstyle.md)

<br>

## How to Contribute

See [the contribution guide](/CONTRIBUTING.md).

<br>

## Run Rector in Docker

You can run Rector on your project using Docker:

```bash
docker run --rm -v $(pwd):/project rector/rector:latest process /project/src --set symfony40 --dry-run

# Note that a volume is mounted from `pwd` (the current directory) into `/project` which can be accessed later.
```

Using `rector.php`:

```bash
docker run --rm -v $(pwd):/project rector/rector:latest process /project/app \
    --config /project/rector.php \
    --autoload-file /project/vendor/autoload.php \
    --dry-run
```

<br>

### Debugging

1. Make sure XDebug is installed and configured
2. Add `--xdebug` option when running Rector

Without XDebug, you can use `--debug` option, that will print nested exceptions output.

<br>

## Community Packages

Do you use Rector to upgrade your code? Add it here:

- [palantirnet/drupal-rector](https://github.com/palantirnet/drupal-rector) by [Palantir.net](https://github.com/palantirnet) for [Drupal](https://www.drupal.org/)
- [sabbelasichon/typo3-rector](https://github.com/sabbelasichon/typo3-rector) for [TYPO3](https://typo3.org/)

## Known Drawbacks

### How to Apply Coding Standards?

Rector uses [nikic/php-parser](https://github.com/nikic/PHP-Parser/), that build on technology called *abstract syntax tree* (AST). AST doesn't care about spaces and produces mall-formatted code in both PHP and docblock annotations. **That's why your project needs to have coding standard tool** and set of rules, so it can make refactored nice and shiny again.

Don't have any coding standard tool? Add [ECS](https://github.com/Symplify/EasyCodingStandard) and use prepared [`ecs-after-rector.php`](/ecs-after-rector.php) set.
