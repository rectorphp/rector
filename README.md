# Rector - Reconstruct your Legacy Code to Modern Codebase 

[![Build Status](https://img.shields.io/travis/TomasVotruba/Rector/master.svg?style=flat-square)](https://travis-ci.org/TomasVotruba/Rector)
[![Coverage Status](https://img.shields.io/coveralls/TomasVotruba/Rector/master.svg?style=flat-square)](https://coveralls.io/github/TomasVotruba/Rector?branch=master)

This tool will *reconstruct* (change) your code - **run it only in a new clean git branch**.


## All Reconstructors

### [Nette](https://github.com/nette/)

- `FormCallbackRector`
- `InjectPropertyRector`
- `HtmlAddMethodRector`
- `NetteObjectToSmartTraitRector`
- `RemoveConfiguratorConstantsRector`

### [Symfony](https://github.com/symfony/)

- `NamedServicesToConstructorNodeTraverser`

### Abstract to use

- `AbstractChangeMethodNameRector`


## Install

```bash
composer require rector/rector --dev
```

## Use (WIP)

```bash
vendor/bin/rector reconstruct src --framework Nette --to-version 2.4
vendor/bin/rector reconstruct src --framework Symfony --to-version 3.3
```


### How to Add New Rector

Just extend `Rector\Rector\AbstractRector`.
It will prepare **4 methods** - 2 informative and 2 processing the node.

```php
/**
 * A project that is related to this.
 * E.g "Nette", "Symfony"
 * Use constants from @see SetNames, if possible.
 */
public function getSetName(): string
{
}

/**
 * Version this deprecations is active since.
 * E.g. 2.3.
 */
public function sinceVersion(): float
{
}


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

4. Submit PR
 
5. :check: 



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
