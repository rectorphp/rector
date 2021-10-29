[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/schreiberten)

## Caution

Never run this tool on production, only on development environment where code is under version control (e.g. git). Always review and test automatic changes before releasing to production.

# Rector for TYPO3

This repository (`ssch/typo3-rector`) is for development TYPO3 Rector only.
Head to [`rectorphp/rector`](http://github.com/rectorphp/rector) for installation.

Apply automatic fixes on your TYPO3 code.

[![Downloads](https://img.shields.io/packagist/dt/ssch/typo3-rector.svg?style=flat-square)](https://packagist.org/packages/ssch/typo3-rector)

[Rector](https://github.com/rectorphp/rector) aims to provide instant upgrades and instant refactoring of any PHP 5.3+ code. This project adds rectors specific to TYPO3 to help you migrate between TYPO3 releases or keep your code deprecation free.

## Table of Contents
1. [Examples in action](docs/examples_in_action.md)
1. [Overview of all rules](docs/all_rectors_overview.md)
1. [Installation](docs/installation.md)
1. [Configuration and Processing](docs/configuration_and_processing.md)
1. [Best practice guide](docs/best_practice_guide.md)
1. [Beyond PHP - Entering the realm of FileProcessors](docs/beyond_php_file_processors.md)
1. [Limitations](docs/limitations.md)
1. [Contribution](docs/contribution.md)

Please also have a look at the documentation for [Rector](https://github.com/rectorphp/rector) itself.

## Support
Please post questions to TYPO3 Slack (https://typo3.slack.com) in the channel #ext-typo3-rector.
Or feel free to open an issue or start a discussion on github.

## Credits

Many thanks to [Tomas Votruba](https://tomasvotruba.com) for his on going support and [Rector](https://github.com/rectorphp/rector).
Many thanks to every other contributor.

Oh, and if you've come down this far, you might as well follow me on [twitter](https://twitter.com/schreiberten).

## Known Drawbacks

### How to Apply Coding Standards?

Rector uses [nikic/php-parser](https://github.com/nikic/PHP-Parser/), built on technology called an *abstract syntax tree* (AST). An AST doesn't know about spaces and when written to a file it produces poorly formatted code in both PHP and docblock annotations. **That's why your project needs to have a coding standard tool** and a set of formatting rules, so it can make Rector's output code nice and shiny again.

We're using [ECS](https://github.com/symplify/easy-coding-standard) with [this setup](ecs.php).
