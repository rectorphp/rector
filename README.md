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
- Complete [@var annotations or parameter/return type declarations](https://www.tomasvotruba.com/blog/2019/01/03/how-to-complete-type-declarations-without-docblocks-with-rector/)
- [Complete PHP 7.4 property type declarations](https://www.tomasvotruba.com/blog/2018/11/15/how-to-get-php-74-typed-properties-to-your-code-in-few-seconds/)
- Upgrade your code from **PHP 5.3 to 8.0**
- [Migrate your project from Nette to Symfony](https://www.tomasvotruba.com/blog/2019/02/21/how-we-migrated-from-nette-to-symfony-in-3-weeks-part-1/)
- [Refactor Laravel facades to dependency injection](https://www.tomasvotruba.com/blog/2019/03/04/how-to-turn-laravel-from-static-to-dependency-injection-in-one-day/)
- And much more...

## How to Apply Coding Standards?

Rector uses [nikic/php-parser](https://github.com/nikic/PHP-Parser/), that build on technology called *abstract syntax tree*) technology* (AST). AST doesn't care about spaces and produces mall-formatted code. That's why your project needs to have coding standard tool and set of rules, so it can make refactored nice and shiny again.

Don't have any coding standard tool? Add [EasyCodingStandard](https://github.com/Symplify/EasyCodingStandard) and use prepared [`ecs-after-rector.php`](/ecs-after-rector.php) set.

## Install

```bash
composer require rector/rector --dev
```

- Having conflicts during `composer require`? → Use the [Rector Prefixed](https://github.com/rectorphp/rector-prefixed)
- Using a different PHP version than Rector supports? → Use the [Docker image](#run-rector-in-docker)

## Running Rector

### A. Prepared Sets

Featured open-source projects have **prepared sets**. You can find them in [`/config/set`](/config/set) or by autocomplete of [`Rector\Set\ValueObject\SetList`](/packages/set/src/ValueObject/SetList.php) constants in `rector.php` config.

Let's say you pick the [`symfony40`](/config/set/symfony40.php) set and you want to upgrade your `/src` directory:

```bash
vendor/bin/rector process src --set symfony40 --dry-run
```

Rector will show you diff of files that it *would* change. To *make* the changes, drop `--dry-run`:

```bash
# apply upgrades to your code
vendor/bin/rector process src --set symfony40
```

Some sets, such as [`code-quality`](/config/set/code-quality.php) can be used on a regular basis. **The best practice is to  use config over command line**:

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [SetList::CODE_QUALITY]);
};
```

PHP config format is a new [Symfony best practice](https://twitter.com/symfony_en/status/1284538366147678208).

### B. Standalone Rules

In the end, it's best to combine few of basic sets and drop [particular rules](/docs/rector_rules_overview.md) that you want to try:

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Php74\Rector\Property\TypedPropertyRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class);

    $parameters->set(Option::SETS, [SetList::CODE_QUALITY]);
};
```

Then let Rector refactor your code:

```bash
vendor/bin/rector process src
```

:+1:

<br>

*Note: `rector.php` is loaded by default. For different location, use `--config` option.*

## Features

### Paths

If you're annoyed by repeating paths in arguments, you can move them to config instead:

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
};
```

### Extra Autoloading

Rector relies on whatever autoload setup the project it is fixing is using by using the Composer autoloader as default. To specify your own autoload file, use `--autoload-file` option:

```bash
vendor/bin/rector process ../project --autoload-file ../project/vendor/autoload.php
```

Or use a `rector.php` configuration file:

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/vendor/squizlabs/php_codesniffer/autoload.php',
        __DIR__ . '/vendor/project-without-composer',
    ]);
};
```

### Exclude Paths and Rectors

You can also **exclude files or directories** (with regex or [fnmatch](http://php.net/manual/en/function.fnmatch.php)):

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::EXCLUDE_PATHS, [
        __DIR__ . '/src/*/Tests/*',
    ]);
};
```

You can use a whole set, except 1 rule:

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::CODE_QUALITY,
    ]);

    $parameters->set(Option::EXCLUDE_RECTORS, [
        SimplifyIfReturnBoolRector::class,
    ]);
};
```

For in-file exclusion, use `@noRector \FQN name` annotation:

```php
class SomeClass
{
    /**
     * @noRector \Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector
     */
    public function foo()
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
```

Or just short name:

```bash
vendor/bin/rector process src --set solid --only FinalizeClassesWithoutChildrenRector
```

Both will run only `Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector`.

### Provide PHP Version

By default Rector uses the language features matching your system version of PHP. You can configure it for a different PHP version:

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PHP_VERSION_FEATURES, '7.2'); # your version is 7.3
};
```

### Safe Types

In default setting:

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SAFE_TYPES, false);
};
```

All docblocks are taken seriously, e.g. with [typed properties](https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md#typedpropertyrector) rule:

```diff
 <?php

 class ValueObject
 {
-    public $value;
+    public string $value;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
}
```

Do you want to use only explicit PHP type declaration? Enable `safe_types`:

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SAFE_TYPES, true);
};
```

Then, docblocks are skipped:

```diff
 <?php

 class ValueObject
 {
     public $value;

-    public $count;
+    public int $count;

    /**
     * @param string $value
     */
    public function __construct($value, int $count)
    {
        $this->value = $value;
        $this->count = $count
    }
}
```

### Import Use Statements

FQN classes are not imported by default. If you don't want to do it manually after every Rector run, enable it by:

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
};
```

You can also fine-tune how these imports are processed:

```php
<?php
// rector.php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // this will not import root namespace classes, like \DateTime or \Exception
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);
    // this will not import classes used in PHP DocBlocks, like in /** @var \Some\Class */
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);
};
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

    $parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__ .  '/var/cache/dev/AppKernelDevDebugContainer.xml');
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
