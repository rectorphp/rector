# Prefixed Rector

[![Build Status](https://img.shields.io/travis/rectorphp/rector-prefixed/master.svg?style=flat-square)](https://travis-ci.org/rectorphp/rector-prefixed)
[![Downloads](https://img.shields.io/packagist/dt/rector/rector-prefixed.svg?style=flat-square)](https://packagist.org/packages/rector/rector)

Rector **instantly upgrades PHP & YAML code of your application**, with focus on open-source projects.
Read more about it in [original repository](https://github.com/rectorphp/rector).

<br>

Since Rector **uses project's autoload to analyze type of elements**, it cannot be installed as project in standalone directory but needs to be added as dependency. In case you have composer versions conflicts, use this prefixed version.

## Install

```bash
composer require rector/rector-prefixed:@dev --dev
```

## Build & Deploy

Go to [`rector/rector` repository](https://github.com/rectorphp/rector) and run:

```bash
bin/build-prefixed-rector.sh
bin/publish-prefixed-rector.sh
```

## How to Contribute

This is an automatically generated repository. See [`bin/build-prefixed-rector-bin.sh`](https://github.com/rectorphp/rector/blob/master/bin/build-prefixed-rector-bin.sh) in original repository for more.
