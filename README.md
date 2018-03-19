# Rector - Upgrade your Legacy App to Modern Codebase

Rector is under development phase in 2018, to figure out the best way to use it in applications, polish API of Rector classes and get feedback from community. Please consider this while using it and report issues or post ideas you'll come up with while using. Thank you!

When you're gonna move from manual work to **instant upgrades**?

[![Build Status](https://img.shields.io/travis/rectorphp/rector/master.svg?style=flat-square)](https://travis-ci.org/rectorphp/rector)
[![Coverage Status](https://img.shields.io/coveralls/RectorPHP/Rector/master.svg?style=flat-square)](https://coveralls.io/github/rectorphp/rector?branch=master)

![Rector-showcase](docs/images/rector-showcase.gif)

Rector **upgrades your application** for you, with focus on open-source projects:

<p align="center">
    <a href="/config/level/symfony"><img src="/docs/images/symfony.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/level/nette"><img src="/docs/images/nette.png" height="50"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/level/phpunit"><img src="/docs/images/phpunit.jpg"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/level/twig"><img src="/docs/images/twig.png"></a>
</p>


Rector can:

- [Rename classes](/docs/DynamicRectors.md#replace-a-class-name)
- [Rename class' methods](/docs/DynamicRectors.md#change-a-method-name)
- [Rename partial namespace](/docs/DynamicRectors.md#replace-some-part-of-the-namespace)
- [Rename pseudo-namespace to namespace](/docs/DynamicRectors.md#replace-the-underscore-naming-_-with-namespaces-)
- [Add, replace or remove arguments](/docs/DynamicRectors.md#change-argument-value-or-remove-argument)
- [Add typehints based on new types of parent class or interface](/docs/DynamicRectors.md#remove-a-value-object-and-use-simple-type)
- And much more...

## Install

```bash
composer require --dev rector/rector:'dev-master' nikic/php-parser:'4.0.x-dev'
```

### Do you Have Conflicts?

It may also happen your dependencies are in conflict, e.g. [PHPStan](https://github.com/phpstan/phpstan) requires [PHP-Parser](https://github.com/nikic/PHP-Parser) version 3, or older PHPUnit etc. This might be solved in the future, when PHP-Parser version 4 becomes stable.

Since Rector **uses project's autoload to analyze type of elements**, it cannot be installed as project in standalone directory but **needs to be added as dependency**. Here [`bamarni/composer-bin-plugin`](https://github.com/bamarni/composer-bin-plugin) becomes useful:

```bash
composer require bamarni/composer-bin-plugin --dev
```

Then, require Rector using `composer bin`:

```bash
composer bin rector require --dev rector/rector:'dev-master' nikic/php-parser:'4.0.x-dev'
```

And Rector is accessible as:

```bash
vendor/bin/rector
```

### Extra Autoloading

Rector relies on project and autoloading of its classes. To specify own autoload file, use `--autoload-file` option:

```bash
vendor/bin/rector process ../project --autoload-file ../project/vendor/autoload.php
```

## How to Reconstruct your Code

### A. Prepared Sets

Featured open-source projects have **prepared sets**. You'll find them in [`/config/level`](/config/level).

Do you need to upgrade to **Symfony 4.0**, for example?

1. Run rector on your `/src` directory:

    ```bash
    vendor/bin/rector process src --level symfony40
    ```

    Which is a shortcut for using complete path with `--config` option:

    ```bash
    vendor/bin/rector process src --config vendor/rector/rector/src/config/level/symfony/symfony40.yml
    ```

    You can also use your **own config file**:

    ```bash
    vendor/bin/rector process src --config your-own-config.yml
    ```

2. Do you want to see the preview of changes first?

    Use the `--dry-run` option:

    ```bash
    vendor/bin/rector process src --level symfony33 --dry-run
    ```

3. What levels are on the board?

    ```bash
    vendor/bin/rector levels
    ```

### B. Custom Sets

1. Create `rector.yml` with desired Rectors:

    ```yml
    services:
        Rector\Rector\Contrib\Nette\Application\InjectPropertyRector: ~
    ```

2. Try Rector on your `/src` directory:

    ```bash
    vendor/bin/rector process src --dry-run
    ```

3. Apply the changes if you like them:

    ```bash
    vendor/bin/rector process src
    ```

## Configure Rectors for your Case

You don't have to always write PHP code. Many projects change only classes or method names, so it would be too much work for a simple task.

- [Dynamic Rectors](/docs/DynamicRectors.md)
- [Turn Magic to Methods](/docs/MagicDisclosureRectors.md)

## Coding Standards are Outsourced

This package has no intention in formatting your code, as **coding standard tools handle this much better**. We prefer [EasyCodingStandard](https://github.com/Symplify/EasyCodingStandard) with Rector's prepared set:

```bash
# install
composer require --dev symplify/easy-coding-standard

# check
vendor/bin/ecs check --config vendor/rector/rector/ecs-after-rector.neon

# fix
vendor/bin/ecs check --config vendor/rector/rector/ecs-after-rector.neon --fix
```

## More Detailed Documentation

- [How Rector Works?](/docs/HowItWorks.md)
- [How to Create Rector with Fluent Builder](/docs/FluentBuilderRector.md)
- [How to Create Own Rector](/docs/HowToCreateOwnRector.md)
- [Service Name to Type Provider](/docs/ServiceNameToTypeProvider.md)

## How to Contribute

Just follow 3 rules:

- **1 feature per pull-request**
- **New feature needs tests**
- Tests, coding standards and PHPStan **checks must pass**:

    ```bash
    composer complete-check
    ```

    Don you need to fix coding standards? Run:

    ```bash
    composer fix-cs
    ```

We would be happy to merge your feature then.
