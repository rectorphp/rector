# Rector - Reconstruct your Legacy Code to Modern Codebase

[![Build Status](https://img.shields.io/travis/RectorPHP/Rector/master.svg?style=flat-square)](https://travis-ci.org/RectorPHP/Rector)
[![Coverage Status](https://img.shields.io/coveralls/RectorPHP/Rector/master.svg?style=flat-square)](https://coveralls.io/github/RectorPHP/Rector?branch=master)

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

Add to your `composer.json`:

```json
{
    "require-dev": {
        "rector/rector": "@dev",
        "nikic/php-parser": "dev-master#5900d78 as v3.1.1"
    }
}
```

And download packages:

```bash
composer update
```

## How To Reconstruct your Code?

### A. Prepared Sets

Fetaured open-source projects have **prepared sets**. You'll find them in [`/src/config/level`](/src/config/level).

E.g. Do you need to upgrade to Nette 2.4?

1. Run rector on your `/src` directory

```bash
vendor/bin/rector process src --config vendor/bin/rector/src/config/level/nette/nette24.yml
```

Too long? Try `--level` shortcut:

```bash
vendor/bin/rector process src --level nette24
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


### 6 Steps to Add New Rector

Just extend `Rector\Rector\AbstractRector`.
It will prepare **2 methods** processing the node.

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

4. Add to specific level, e.g. [`/src/config/level/nette/nette24.yml`](/src/config/level/nette/nette24.yml)

5. Submit PR

6. :+1:


### Simpler setup with Dynamic Rectors

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
            'BetterReflection': 'Roave\BetterReflection'
    ```

- **change method name**

    ```yml
    # nette24.yml
    rectors:
        Rector\Rector\Dynamic\MethodNameReplacerRector:
            # class:
            #   old method: new method
            'Nette\Utils\Html':
                'add': 'addHtml'

            # or in case of static methods calls

            # class:
            #   old method: [new class, new method]
            'Nette\Bridges\FormsLatte\FormMacros':
                'renderFormBegin': ['Nette\Bridges\FormsLatte\Runtime', 'renderFormBegin']
    ```

- **change property name**

    ```yml
    # php-parser40.yml
    rectors:
        Rector\Rector\Dynamic\PropertyNameReplacerRector:
            # class:
            #   old property: new property
            'PhpParser\Node\Param':
                'name': 'var'
    ```

- **change class constant name**

    ```yml
    # symfony30.yml
    rectors:
        Rector\Rector\Dynamic\ClassConstantReplacerRector:
            # class:
            #   OLD_CONSTANT: NEW_CONSTANT
            'Symfony\Component\Form\FormEvents':
                'PRE_BIND': 'PRE_SUBMIT'
                'BIND': 'SUBMIT'
                'POST_BIND': 'POST_SUBMIT'
    ```

- **change parameters typehint according to parent type**

    ```yml
    # php-parser40.yml
    rectors:
        Rector\Rector\Dynamic\ParentTypehintedArgumentRector:
            # class:
            #   method:
            #       parameter: typehting
            'PhpParser\Parser':
                'parse':
                    'code': 'string'
    ```

- **remove unused argument**

    ```yml
    Rector\Rector\Dynamic\ArgumentRemoverRector:
        # class:
        #   method:
        #       - argument to remove
        'Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister':
            'getSelectJoinColumnSQL':
                - 'className'
    ```
    
- **change argument value** (@todo improve and unit API to per class and method)

    ```yml
    Rector\Rector\Dynamic\ArgumentReplacerRector:
        # class:s
        #   method:
        #       - 'argument po'
        -
            class: Symfony\Component\DependencyInjection\ContainerBuilder
            method: compile
            position: 0
            type: added
            default_value: false
    ```

- or **replace underscore naming `_` with namespaces `\`**

    ```yml
    rectors:
        Rector\Roector\Dynamic\PseudoNamespaceToNamespaceRector:
            # old namespace prefix
            - 'PHPUnit_'
    ```


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

You must have separated environment with PHP 7.1 (for example in Docker container). When you have it then run following command:

```
composer create-project rector/rector path-to-rector
```

When do you have it then you can run all commands like

```
path-to-rector/bin/rector process /var/www/old-project --config path-to-rector/src/config/level/nette/nette24.yml
# or for short
path-to-rector/bin/rector process /var/www/old-project --level nette24
```
