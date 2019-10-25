# Rector - Upgrade Your Legacy App to a Modern Codebase

Rector is a **rec**onstruc**tor** tool - it does **instant upgrades** and **instant refactoring** of your code.
Why refactor manually if Rector can handle 80% for you?

[![Build Status](https://img.shields.io/travis/rectorphp/rector/master.svg?style=flat-square)](https://travis-ci.org/rectorphp/rector)
[![Coverage Status](https://img.shields.io/coveralls/rectorphp/rector/master.svg?style=flat-square)](https://coveralls.io/github/rectorphp/rector?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/rector/rector.svg?style=flat-square)](https://packagist.org/packages/rector/rector)


![Rector-showcase](docs/images/rector-showcase-var.gif)

<br>

## Sponsors

Rector grows faster with your help, the more you help the more work it saves you.
Check out [Rector's Patreon](https://www.patreon.com/rectorphp). One-time donation is welcomed [through PayPal](https://www.paypal.me/rectorphp).

Thank you:

<a href="https://spaceflow.io/en"><img src="/docs/images/spaceflow.png"></a>

<br>

## Open-Source First

Rector **instantly upgrades and instantly refactors the PHP code of your application**. It supports all modern versions of PHP and many open-source projects:

<br>

<p align="center">
    <a href="/config/set/php"><img src="/docs/images/php.png"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="/config/set/symfony"><img src="/docs/images/symfony.png"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="/config/set/laravel"><img src="/docs/images/laravel.png"></a>
    <br>
    <a href="/config/set/cakephp"><img src="/docs/images/cakephp.png"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="/config/set/phpunit"><img src="/docs/images/phpunit.jpg"></a>
    <img src="/docs/images/space.png" width=40>
    <a href="/config/set/twig"><img src="/docs/images/twig.png"></a>
</p>

<br>

## What Can Rector Do for You?

- Rename classes, methods, properties, namespaces or constants
- Complete [parameter, var or return type declarations](https://www.tomasvotruba.cz/blog/2019/01/03/how-to-complete-type-declarations-without-docblocks-with-rector/) based on static analysis of your code
- Upgrade your code from PHP 5.3 to PHP 7.4
- [Migrate your project from Nette to Symfony](https://www.tomasvotruba.cz/blog/2019/02/21/how-we-migrated-from-nette-to-symfony-in-3-weeks-part-1/)
- [Complete PHP 7.4 property type declarations](https://www.tomasvotruba.cz/blog/2018/11/15/how-to-get-php-74-typed-properties-to-your-code-in-few-seconds/)
- [Refactor Laravel facades to dependency injection](https://www.tomasvotruba.cz/blog/2019/03/04/how-to-turn-laravel-from-static-to-dependency-injection-in-one-day/)
- And much more...

...**look at the overview of [all available Rectors](/docs/AllRectorsOverview.md)** with before/after diffs and configuration examples. You can use them to build your own sets.

## How to Apply Coding Standards?

The AST libraries that Rector uses aren't well-suited for coding standards, so it's better to let coding standard tools do that.

Don't have a coding standard tool for your project? Consider adding [EasyCodingStandard](https://github.com/Symplify/EasyCodingStandard), [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) or [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

## Install

```bash
composer require rector/rector --dev
```

**Did you have conflicts during `composer require` or on run? Use the [Docker image](#run-rector-in-docker).**

### Extra Autoloading

Rector relies on project and autoloading of its classes. To specify your own autoload file, use `--autoload-file` option:

```bash
vendor/bin/rector process ../project --autoload-file ../project/vendor/autoload.php
```

Or use a `rector.yaml` config file:

```yaml
# rector.yaml
parameters:
    autoload_paths:
        - 'vendor/squizlabs/php_codesniffer/autoload.php'
        - 'vendor/project-without-composer'
```

## Exclude Paths and Rectors

You can also **exclude files or directories** (with regex or [fnmatch](http://php.net/manual/en/function.fnmatch.php)):

```yaml
# rector.yaml
parameters:
    exclude_paths:
        - '*/src/*/Tests/*'
```

You can use a whole ruleset, except one rule:

```yaml
# rector.yaml
parameters:
    exclude_rectors:
        - 'Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector'
```

By default Rector uses the language features matching your system version of PHP. You can configure it for a different PHP version:

```yaml
# rector.yaml
parameters:
    php_version_features: '7.2' # your version is 7.3
```

You can import FQN class names automatically every time Rector performs a change, so you don't have to do it manually/after each run:

```yaml
# rector.yaml
parameters:
    auto_import_names: true
```

## Running Rector

### A. Prepared Sets

Featured open-source projects have **prepared sets**. You can find them in [`/config/set`](/config/set) or by running:

```bash
vendor/bin/rector sets
```

Let's say you pick the `symfony40` set and you want to upgrade your `/src` directory:

```bash
# show a list of known changes in Symfony 4.0
vendor/bin/rector process src --set symfony40 --dry-run
```

```bash
# apply upgrades to your code
vendor/bin/rector process src --set symfony40
```

Some sets, such as [`code-quality`](/config/set/code-quality) can be
used on a regular basis. You can include them in your `rector.yaml` to
run them by default:

```yaml
# rector.yaml
imports:
    - { resource: 'vendor/rector/rector/config/set/code-quality/*.yaml' }
    - { resource: 'vendor/rector/rector/config/set/php/php71.yaml' }
    - { resource: 'vendor/rector/rector/config/set/php/php72.yaml' }
    - { resource: 'vendor/rector/rector/config/set/php/php73.yaml' }
```

>  If you use Rector in Docker, you can use absolute path, e.g.
>  `/rector/config/set/php/php71.yaml`

### B. Custom Sets

1. Create a `rector.yaml` config file with your desired Rectors:

    ```yaml
    services:
        Rector\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector:
            $annotation: "inject"
    ```

2. Run Rector on your `/src` directory:

    ```bash
    vendor/bin/rector process src --dry-run
    # apply
    vendor/bin/rector process src
    ```

## 3 Steps to Create Your Own Rector

First, make sure it's not covered by [any existing Rectors](/docs/AllRectorsOverview.md).

Let's say we want to **change method prefixes from `set*` to `change*`**.

```diff
 $user = new User();
-$user->setPassword('123456');
+$user->changePassword('123456');
```

### 1. Create a New Rector and Implement Methods

Create a class that extends [`Rector\Rector\AbstractRector`](/src/Rector/AbstractRector.php). It will inherit useful methods e.g. to check node type and name. See the source (or type `$this->` in an IDE) for a list of available methods.

```php
<?php

declare(strict_types=1);

namespace App\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class MyFirstRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        // what does this do?
        // minimalistic before/after sample - to explain in code
        return new RectorDefinition('Change method calls from set* to change*.', [
            new CodeSample('$user->setPassword("123456");', '$user->changePassword("123456");')
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        // what node types we look for?
        // pick any node from https://github.com/rectorphp/rector/blob/master/docs/NodesOverview.md
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node - we can add "MethodCall" type here, because only this node is in "getNodeTypes()"
     */
    public function refactor(Node $node): ?Node
    {
        // we only care about "set*" method names
        if (! $this->isName($node, 'set*')) {
            // return null to skip it
            return null;
        }

        $methodCallName = $this->getName($node);
        $newMethodCallName = Strings::replace($methodCallName, '#^set#', 'change');

        $node->name = new Identifier($newMethodCallName);

        // return $node if you modified it
        return $node;
    }
}
```

### 2. Register It

```yaml
# rector.yaml
services:
    App\Rector\MyFirstRector: ~
```

### 3. Let Rector Refactor Your Code

```bash
# see the diff first
vendor/bin/rector process src --dry-run

# if it's ok, apply
vendor/bin/rector process src
```

That's it!

## More Detailed Documentation

- **[All Rectors: Overview](/docs/AllRectorsOverview.md)**
- [How Does Rector Work?](/docs/HowItWorks.md)
- [Nodes: Overview](/docs/NodesOverview.md)

## How to Contribute

Just follow 3 rules:

- **1 feature per pull-request**
- **New features need tests**
- Tests, coding standards and PHPStan **checks must pass**:

    ```bash
    composer complete-check
    ```

    Do you need to fix coding standards? Run:

    ```bash
    composer fix-cs
    ```

We would be happy to accept PRs that follow these guidelines.

## Run Rector in Docker
You can run Rector on your project using Docker:

```bash
docker run -v $(pwd):/project rector/rector:latest process /project/src --set symfony40 --dry-run

# Note that a volume is mounted from the current directory into `/project` which can be accessed later.
```

Using `rector.yaml`:

```bash
docker run -v $(pwd):/project rector/rector:latest process /project/app --config /project/rector.yaml --autoload-file /project/vendor/autoload.php --dry-run
```

### Community Packages

Do you use Rector to upgrade your code? Add it here:

- [drupal8-rector/drupal8-rector](https://github.com/drupal8-rector/drupal8-rector) by [@mxr576](https://github.com/mxr576) for [Drupal](https://www.drupal.org/)
- [sabbelasichon/typo3-rector](https://github.com/sabbelasichon/typo3-rector) for [TYPO3](https://typo3.org/)
