# Rector - Reconstruct your Legacy Code to Modern Codebase 

[![Build Status](https://img.shields.io/travis/RectorPHP/Rector/master.svg?style=flat-square)](https://travis-ci.org/RectorPHP/Rector)
[![Coverage Status](https://img.shields.io/coveralls/RectorPHP/Rector/master.svg?style=flat-square)](https://coveralls.io/github/RectorPHP/Rector?branch=master)

This tool will **upgrade your application** for you.

## All Reconstructors

At the moment these packages are supported:

- [Nette](/src/Rector/Contrib/Nette)
- [Symfony](/src/Rector/Contrib/Symfony)
- [PHPUnit](/src/Rector/Contrib/PHPUnit)
- [PHP_CodeSniffer](/src/Rector/Contrib/PHP_CodeSniffer)


## Install

```bash
composer require rector/rector --dev
```

## How To Reconstruct your Code?

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


### READMEs for Subpackages

- [DeprecationExtractor](/packages/DeprecationExtractor/README.md)
- [NodeTraverserQueue](/packages/NodeTraverserQueue/README.md)
- [NodeTypeResolver](/packages/NodeTypeResolver/README.md)
- [NodeValueResolver](/packages/NodeValueResolver/README.md)

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
