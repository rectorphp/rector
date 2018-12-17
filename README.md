# Rector - Upgrade your Legacy App to Modern Codebase

Rector is a **rec**onstruc**tor** tool - it does **instant upgrades** and **instant refactoring** of your code.
I mean, why do it manually if 80 % can Rector handle for you?

[![Build Status](https://img.shields.io/travis/rectorphp/rector/master.svg?style=flat-square)](https://travis-ci.org/rectorphp/rector)
[![Coverage Status](https://img.shields.io/coveralls/rectorphp/rector/master.svg?style=flat-square)](https://coveralls.io/github/rectorphp/rector?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/rector/rector.svg?style=flat-square)](https://packagist.org/packages/rector/rector)


![Rector-showcase](docs/images/rector-showcase.gif)

Rector **instantly upgrades PHP & YAML code of your application**, with focus on open-source projects:

<br>

<p align="center">
    <a href="/config/level/php"><img src="/docs/images/php.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/level/cakephp"><img src="/docs/images/cakephp.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/level/symfony"><img src="/docs/images/symfony.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/level/sylius"><img src="/docs/images/sylius.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/level/phpunit"><img src="/docs/images/phpunit.jpg"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/level/twig"><img src="/docs/images/twig.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/level/silverstripe"><img src="/docs/images/silverstripe.jpg"></a>
</p>

<br>

**Rector can**:

- Rename classes, methods and properties
- Rename partial namespace
- Rename pseudo-namespace to namespace
- Add, replace or remove arguments
- Add arguments or return typehint
- Change visibility of constant, property or method
- And much more...

...**look at overview of [all available Rectors](/docs/AllRectorsOverview.md)** with before/after diffs and configuration examples. You can use them to build your own sets.

## Install

```bash
composer require rector/rector --dev
```

**Do you have conflicts on `composer require`?**

Install [prefixed version](https://github.com/rectorphp/rector-prefixed) with isolated dependencies.

### Extra Autoloading

Rector relies on project and autoloading of its classes. To specify own autoload file, use `--autoload-file` option:

```bash
vendor/bin/rector process ../project --autoload-file ../project/vendor/autoload.php
```

Or make use of `rector.yml` config:

```yaml
# rector.yml
parameters:
    autoload_paths:
        - 'vendor/squizlabs/php_codesniffer/autoload.php'
        - 'vendor/project-without-composer'
```

You can also **exclude files or directories** (with regex or [fnmatch](http://php.net/manual/en/function.fnmatch.php)):

```yaml
# rector.yml
parameters:
    exclude_paths:
        - '*/src/*/Tests/*'
```

## Running Rector

### A. Prepared Sets

Featured open-source projects have **prepared sets**. You'll find them in [`/config/level`](/config/level) or by calling:

```bash
vendor/bin/rector levels
```

Let's say you pick `symfony40` level and you want to upgrade your `/src` directory:

```bash
# show known changes in Symfony 4.0
vendor/bin/rector process src --level symfony40 --dry-run
```

```bash
# apply
vendor/bin/rector process src --level symfony40
```

Tip: To process just specific subdirectories, you can use [fnmatch](http://php.net/manual/en/function.fnmatch.php) pattern:

```bash
vendor/bin/rector process "src/Symfony/Component/*/Tests" --level phpunit60 --dry-run
```

### B. Custom Sets

1. Create `rector.yml` with desired Rectors:

    ```yaml
    services:
        Rector\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector:
            $annotation: "inject"
    ```

2. Run on your `/src` directory:

    ```bash
    vendor/bin/rector process src --dry-run
    # apply
    vendor/bin/rector process src
    ```

## How to Apply Coding Standards?

AST that Rector uses doesn't deal with coding standards very well, so it's better to let coding standard tools do that. Your project doesn't have one? Rector ships with [EasyCodingStandard](https://github.com/Symplify/EasyCodingStandard) set that covers namespaces import, 1 empty line between class elements etc.

Just use `--with-style` option to handle these basic cases:

```bash
vendor/bin/rector process src --with-style
```

## More Detailed Documentation

- **[All Rectors Overview](/docs/AllRectorsOverview.md)**
- [How Rector Works?](/docs/HowItWorks.md)
- [How to Create Own Rector](/docs/HowToCreateOwnRector.md)

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

## Run rector in docker
With this command, you can process your project with rector from docker:
```bash
docker run -v $(pwd):/project rector/rector:latest
```
