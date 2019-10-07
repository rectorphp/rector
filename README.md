[![Build Status](https://img.shields.io/travis/rectorphp/rector/master.svg?style=flat-square)](https://travis-ci.org/rectorphp/rector)
[![Coverage Status](https://img.shields.io/coveralls/rectorphp/rector/master.svg?style=flat-square)](https://coveralls.io/github/rectorphp/rector?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/rector/rector.svg?style=flat-square)](https://packagist.org/packages/rector/rector)

# Rector

<a href="https://getrector.org/">
<img height="100" style="max-width:100%;" src="https://getrector.org/assets/images/logo/rector-no_frame_vector.svg">
</a>

<br>

Rector is a **rec**onstruc**tor** tool - it does **instant upgrades** and **instant refactoring** of your code
and helps you to upgrade from a legacy to modern codebase.

Why doing it manually if 80% Rector can handle for you?

#### Index

* [Showcase](#showcase)
* [Crowdfunding](#crowdfunding)
* [Sponsors](#sponsors)
* [Overview](#overview)
* [Features](#features)
* [Rector !== Coding Standards](rector--coding-standards)
* [Install](install)
* [Configuration](configuration)
    * [Config Loading]()
    * [Extra Autoloading]()
    * [Exclude files / directories]()
    * [Exclude Rectors]()
    * [Define the PHP version]()
* [Usage]()
    * [A. Prepared Sets]()
    * [B. Custom Sets]()
* [Create your own Rector (in 3 steps)]()
    * [1. Create]()
    * [2. Register]()
    * [3. Use]()
* [Documentation]()
* [Contribute]()
    * [Run checks]()
    * [Auto fix coding standards]()
* [Docker]()
* [Community]()

#### Showcase

![Rector-showcase](docs/images/rector-showcase.gif)

<br>

#### Crowdfunding

Rector can grow faster with your help, the more you help, the more work it saves for us all.

So please support the project on [Patreon](https://www.patreon.com/rectorphp) or say thanks with a one-time donation through [PayPal](https://www.paypal.me/rectorphp).

<br>

#### Sponsors

A big thank you to our sponsors: ❤

<a href="https://spaceflow.io/en"><img src="/docs/images/spaceflow.png"></a>

<br>

#### Overview

Rector already includes support for many PHP open source projects and for PHP itself. 

We call this upgrades and refactoring classes **Rectors** or *Rector* (e.g. AnnotatedPropertyInjectToConstructorInjectionRector) for a single class. 
A full set of this classes we call **Set** (e.g. symfony40).

<br>

<p align="center">
    <a href="/config/set/php"><img src="/docs/images/php.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/set/cakephp"><img src="/docs/images/cakephp.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/set/symfony"><img src="/docs/images/symfony.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/set/easy-corp"><img src="/docs/images/easy-admin.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/set/sylius"><img src="/docs/images/sylius.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/set/phpunit"><img src="/docs/images/phpunit.jpg"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/set/twig"><img src="/docs/images/twig.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/set/laravel"><img src="/docs/images/laravel.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/config/set/silverstripe"><img src="/docs/images/silverstripe.jpg"></a>
</p>

Please take look at the overview of all available [Rectors](/docs/AllRectorsOverview.md) with before/after diffs and configuration examples.

<br>

#### Features

- Rename classes, methods, properties, namespaces or constants
- Complete [parameter, var or return type declarations](https://www.tomasvotruba.cz/blog/2019/01/03/how-to-complete-type-declarations-without-docblocks-with-rector/) based on static analysis of your code
- Upgrade your code from PHP 5.3 to PHP 7.4
- [Migrate your project from Nette to Symfony](https://www.tomasvotruba.cz/blog/2019/02/21/how-we-migrated-from-nette-to-symfony-in-3-weeks-part-1/)
- [Complete PHP 7.4 property type declarations](https://www.tomasvotruba.cz/blog/2018/11/15/how-to-get-php-74-typed-properties-to-your-code-in-few-seconds/)
- [Turn Laravel static to Dependency Injection](https://www.tomasvotruba.cz/blog/2019/03/04/how-to-turn-laravel-from-static-to-dependency-injection-in-one-day/)
- ...

<br>

#### Rector !== Coding Standards

Don't use Rector for your coding standards.

The AST libraries that Rector uses, don't work well with coding standards, so it's better to let coding standard tools do that.
Your project doesn't have one? Consider adding [EasyCodingStandard](https://github.com/Symplify/EasyCodingStandard), [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) or [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

<br>

## Install

```bash
composer require rector/rector --dev
```

INFO: If you have conflicts on `composer require` or other dependencies problems, 
then you can use this [Docker](#docker) image.

<br>

## Configuration

#### Config Loading

The config file `rector.yaml` is passed to the rector executable with the `--config` option:

```bash
vendor/bin/rector process ../project --config ../project/rector.yaml
```

#### Extra Autoloading

Rector relies on project and autoloading of its classes. To specify your own autoload file(s), use the `autoload_paths` option in the config:

```yaml
# rector.yaml
parameters:
    autoload_paths:
        - 'vendor/squizlabs/php_codesniffer/autoload.php'
        - 'vendor/project-without-composer'
```

Or make use of `--autoload-file` option:

```bash
vendor/bin/rector process ../project --config ../project/rector.yaml --autoload-file ../project/vendor/autoload.php
```

#### Exclude files / directories

You can also exclude files or directories (with regex or [fnmatch](http://php.net/manual/en/function.fnmatch.php)):

```yaml
# rector.yaml
parameters:
    exclude_paths:
        - '*/src/*/Tests/*'
```

#### Exclude Rectors

Do you want to use a whole set, except that one rule? Exclude it:

```yaml
# rector.yaml
parameters:
    exclude_rectors:
        - 'Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector'
```

#### Define the PHP version

By default Rector uses language features of your current PHP version. If you you want to use a different PHP version, please use this option:

```yaml
parameters:
    php_version_features: '7.2'
```

<br>

## Usage

#### A. Prepared Sets

You can see all included sets for different PHP open source projects and for PHP itself in [`/config/set`](/config/set) or by calling:

```bash
vendor/bin/rector sets
```

Let's say you pick the `symfony40` set and you want to upgrade your `./src` directory:

```bash
# see the diff first
vendor/bin/rector process src --set symfony40 --dry-run

# if it's ok, apply the changes
vendor/bin/rector process src --set symfony40
```

#### B. Custom Sets

1. Create `./rector.yaml` with desired Rectors:

    ```yaml
    services:
        Rector\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector:
            $annotation: "inject"
    ```

2. Run on your `./src` directory:

```bash
## see the diff first
vendor/bin/rector process src --dry-run

# if it's ok, apply the changes
vendor/bin/rector process src
```

<br>

## Create your own Rector (in 3 steps)

First, make sure it's not covered by any existing [Rectors](/docs/AllRectorsOverview.md) yet.

Let's say we want to **change method calls from `set*` to `change*`**.

```diff
 $user = new User();
-$user->setPassword('123456');
+$user->changePassword('123456');
```

#### 1. Create

Let's create a new class (*./src/App/Rector/MyFirstRector.php*) that extends [`Rector\Rector\AbstractRector`](/src/Rector/AbstractRector.php).
It has useful methods like checking node type and name. Just run `$this->` and let PHPStorm show you all possible methods.

```php
<?php declare(strict_types=1);

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
        // description + minimalistic before/after sample - to explain in code
        return new RectorDefinition('Change method calls from set* to change*.', [
            new CodeSample('$user->setPassword("123456");', '$user->changePassword("123456");')
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        // node types we look for
        //
        // INFO: pick any node from: https://github.com/rectorphp/rector/blob/master/docs/NodesOverview.md
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

        // return modified $node
        return $node;
    }
}
```

#### 2. Register

```yaml
# rector.yaml
services:
    App\Rector\MyFirstRector: ~
```

#### 3. Use

Let the new Rector refactor your code base.

```bash
# see the diff first
vendor/bin/rector process src --dry-run

# if it's ok, apply the changes
vendor/bin/rector process src
```

That's it!

<br>

## Documentation

- [All Rectors Overview](/docs/AllRectorsOverview.md)
- [How Rector Works?](/docs/HowItWorks.md)
- [Nodes Overview](/docs/NodesOverview.md)

<br>

## Contribute

Just follow 3 rules:

- 1 feature === 1 pull-request
- New features === New tests
- Tests, coding standards and PHPStan **checks must pass**:

#### Run checks

```bash
composer complete-check
```

#### Auto fix coding standards

```bash
composer fix-cs
```

May the **rec**onstruc**tor** be with you and we would be happy to discuss and merge your ideas.

<br>

## Docker

With this command, you can process your project with Rector from docker:

```bash
docker run -v $(pwd):/project rector/rector:latest process /project/src --set symfony40 --dry-run

# Note that a volume is mounted from `pwd` into `/project` which can be accessed later.
```

Using `rector.yaml`:

```bash
docker run -v $(pwd):/project rector/rector:latest process /project/app --config /project/rector.yaml --autoload-file /project/vendor/autoload.php --dry-run
```

<br>

## Community

If you are using Rector to upgrade your code, please share your experience and maybe your code:

- [drupal8-rector/drupal8-rector](https://github.com/drupal8-rector/drupal8-rector) by @mxr576
