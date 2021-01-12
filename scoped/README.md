# Prefixed Rector

[![Build Status Github Actions](https://img.shields.io/github/workflow/status/rectorphp/rector-prefixed/Code_Checks?style=flat-square)](https://github.com/rectorphp/rector-prefixed/actions)
[![Downloads](https://img.shields.io/packagist/dt/rector/rector-prefixed.svg?style=flat-square)](https://packagist.org/packages/rector/rector-prefixed)

Do you have conflicts on Rector install? You're in the right place.

Prefixed Rector can [be installed even on very old Symfony](https://getrector.org/blog/2020/01/20/how-to-install-rector-despite-composer-conflicts).

## Install

```bash
composer require rector/rector-prefixed --dev
```

```bash
# generate "rector.php" config
vendor/bin/rector init

# dry run
vendor/bin/rector process src --dry-run

# changing run
vendor/bin/rector process src
```
