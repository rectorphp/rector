# Rector - Reconstruct your Legacy Code to Modern Codebase 

[![Build Status](https://img.shields.io/travis/TomasVotruba/Rector/master.svg?style=flat-square)](https://travis-ci.org/TomasVotruba/Rector)
[![Coverage Status](https://img.shields.io/coveralls/TomasVotruba/Rector/master.svg?style=flat-square)](https://coveralls.io/github/TomasVotruba/Rector?branch=master)


This tool will *reconstruct* (change) your code - **run it only in a new clean git branch**.


## All Reconstructors

- `InjectAnnotationToConstructorReconstructor` ([Nette](https://github.com/nette/))
- `NamedServicesToConstructorReconstructor` ([Symfony](https://github.com/symfony/))


## Install

```bash
composer require rector/rector --dev
```

## Use

```bash
vendor/bin/rector reconstruct src
```

### How to Contribute

Just follow 3 rules:

- **1 feature per pull-request**
- **New feature needs tests**. [Coveralls.io](https://coveralls.io/) checks code coverage under every PR.
- Tests, coding standard and PHPStan **checks must pass**

    ```bash
    composer all
    ```

    Often you don't need to fix coding standard manually, just run:

    ```bash
    composer fs
    ```

We would be happy to merge your feature then.
