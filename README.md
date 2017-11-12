# Rector Reconstructs your Legacy Code to Modern Codebase

[![Build Status](https://img.shields.io/travis/rectorphp/rector/master.svg?style=flat-square)](https://travis-ci.org/RectorPHP/Rector)
[![Coverage Status](https://img.shields.io/coveralls/rectorphp/rector/master.svg?style=flat-square)](https://coveralls.io/github/rectorphp/rector?branch=master)

Rector **upgrades your application** for you, with focus on open-source projects:

<p align="center">
    <a href="/src/config/level/symfony"><img src="/docs/symfony.png"></a>
    <img src="/docs/space.png">
    <a href="/src/config/level/nette"><img src="/docs/nette.png" height="50"></a>
    <img src="/docs/space.png">
    <a href="/src/config/level/phpunit"><img src="/docs/phpunit.jpg"></a>
    <img src="/docs/space.png">
    <a href="/src/config/level/roave"><img src="/docs/roave.png"></a>
</p>

<br>

## Install

```bash
composer require --dev rector/rector @dev nikic/php-parser 'dev-master#5900d78 as v3.1.1'
```

## How To Reconstruct your Code?

### A. Prepared Sets

Fetaured open-source projects have **prepared sets**. You'll find them in [`/src/config/level`](/src/config/level).

E.g. Do you need to upgrade to Symfony 3.3?

1. Run rector on your `/src` directory

```bash
vendor/bin/rector process src --level symfony33
```

Which is just a shortcut for using complete path with `--config` option:
```bash
vendor/bin/rector process src --config vendor/rector/rector/src/config/level/symfony/symfony33.yml
```

You can also use your own config file:

```bash
vendor/bin/rector process src --config your-own-config.yml
```

2. Check the Git

```
git diff
```


### B. Custom Sets

1. Create `rector.yml` with desired Rectors

```yml
rectors:
    - Rector\Rector\Contrib\Nette\Application\InjectPropertyRector
```

2. Run rector on your `/src` directory

```bash
vendor/bin/rector process src
```

3. Check the Git

```
git diff
```


### Simple setup with Dynamic Rectors

You don't have to always write PHP code. Many projects change only classes or method names, so it would be too much work for a simple task.

Instead you can use prepared **Dynamic Rectors** directly in `*.yml` config:

You can:

- **replace class name**

    ```yml
    # phpunit60.yml
    rectors:
        Rector\Rector\Dynamic\ClassReplacerRector:
            # old class: new class
            'PHPUnit_Framework_TestCase': 'PHPUnit\Framework\TestCase'
    ```

- **replace part of namespace**

    ```yml
    # better-reflection20.yml
    rectors:
        Rector\Rector\Dynamic\NamespaceReplacerRector:
            # old namespace: new namespace
            'BetterReflection': 'Roave\BetterReflection'
    ```

- **change method name**

    ```yml
    rectors:
        Rector\Rector\Dynamic\MethodNameReplacerRector:
            # class
            'Nette\Utils\Html':
                # old method: new method
                'add': 'addHtml'

            # or in case of static methods calls

            # class
            'Nette\Bridges\FormsLatte\FormMacros':
                # old method: [new class, new method]
                'renderFormBegin': ['Nette\Bridges\FormsLatte\Runtime', 'renderFormBegin']
    ```

- **change property name**

    ```yml
    rectors:
        Rector\Rector\Dynamic\PropertyNameReplacerRector:
            # class:
            #   old property: new property
            'PhpParser\Node\Param':
                'name': 'var'
    ```

- **change class constant name**

    ```yml
    rectors:
        Rector\Rector\Dynamic\ClassConstantReplacerRector:
            # class
            'Symfony\Component\Form\FormEvents':
                # old constant: new constant
                'PRE_BIND': 'PRE_SUBMIT'
                'BIND': 'SUBMIT'
                'POST_BIND': 'POST_SUBMIT'
    ```

- **change parameters typehint according to parent type**

    ```yml
    rectors:
        Rector\Rector\Dynamic\ParentTypehintedArgumentRector:
            # class
            'PhpParser\Parser':
                # method
                'parse':
                    # parameter: typehint
                    'code': 'string'
    ```

- **change argument value**

    ```yml
    rectors:
        Rector\Rector\Dynamic\ArgumentReplacerRector:
            # class
            'Symfony\Component\DependencyInjection\ContainerBuilder':
                # method
                'compile':
                    # argument position
                    0:
                        # added default value
                        ~: false
                        # or remove completely
                        ~: ~
                        # or replace by new value
                        'Symfony\Component\DependencyInjection\ContainerBuilder\ContainerBuilder::SCOPE_PROTOTYPE': false
    ```

- or **replace underscore naming `_` with namespaces `\`**

    ```yml
    rectors:
        Rector\Roector\Dynamic\PseudoNamespaceToNamespaceRector:
            # old namespace prefix
            - 'PHPUnit_'
    ```


### 6 Steps to Add New Rector

In case you need a transformation that you didn't find in Dynamic Rectors, you can create your own:

1. Just extend `Rector\Rector\AbstractRector` class. It will prepare **2 methods**:

```php
public function isCandidate(Node $node): bool
{
}

public function refactor(Node $node): ?Node
{
}
```

2. Put it under `namespace Rector\Contrib\<set>;` namespace

```php
<?php declare(strict_types=1);

namespace Rector\Contrib\Symfony;

use Rector\Rector\AbstractRector;

final class MyRector extends AbstractRector
{
    // ...
}
```

3. Add a Test Case

4. Add to specific level, e.g. [`/src/config/level/symfony/symfony33.yml`](/src/config/level/symfony/symfony33.yml)

5. Submit PR

6. :+1:



### Coding Standards are Outsourced

This package has no intention in formatting your code, as **coding standard tools handle this much better**.

We prefer [EasyCodingStandard](https://github.com/Symplify/EasyCodingStandard) that is already available (no install needed):

```php
# check
vendor/bin/ecs check --config vendor/rector/rector/ecs-after-rector.neon
# fix
vendor/bin/ecs check --config vendor/rector/rector/ecs-after-rector.neon --fix
```

but you can use any other with [this setup](/ecs-after-rector.neon).



### Advanced Operations

- [Service Name to Type Provider](/docs/ServiceNameToTypeProvider.md)


### How to Contribute

Just follow 3 rules:

- **1 feature per pull-request**
- **New feature needs tests**
- Tests, coding standard and PHPStan **checks must pass**

    ```bash
    composer all
    ```

    Don you need to fix coding standards? Run:

    ```bash
    composer fix-cs
    ```

We would be happy to merge your feature then.


### How to use on PHP < 7.1 on Incompatible Composer Dependencies

You must have an isolated environment with PHP 7.1 (for example in a Docker container). When you have it then run following command:

```
composer create-project rector/rector path-to-rector
```

You will be able to run all commands in the following manner:

```
path-to-rector/bin/rector process /var/www/old-project --config path-to-rector/src/config/level/symfony/symfony33.yml
# or for short
path-to-rector/bin/rector process /var/www/old-project --level symfony33
```
