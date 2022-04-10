[![Latest Stable Version](https://poser.pugx.org/ssch/typo3-rector/v/stable.svg)](https://packagist.org/packages/ssch/typo3-rector)
[![Total Downloads](https://poser.pugx.org/ssch/typo3-rector/d/total.svg)](https://packagist.org/packages/ssch/typo3-rector)
[![Monthly Downloads](https://poser.pugx.org/ssch/typo3-rector/d/monthly)](https://packagist.org/packages/ssch/typo3-rector)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/schreiberten)

:heavy_exclamation_mark: *Caution:* Never run this tool on production, only on development environment where code is under version
control (e.g. git). Always review and test automatic changes before releasing to production.

# Rector for TYPO3

This project lets you apply instant upgrades and instant refactoring to your [TYPO3 Core](https://get.typo3.org/) and
[extension](https://extensions.typo3.org) code, making it easier to migrate between TYPO3 releases and keeping your code
free from deprecation.

It extends the [Rector](https://github.com/rectorphp/rector) project, which aims to provide instant upgrades and instant
refactoring for any PHP code (5.3+).

|                    | URL                                                          |
|--------------------|--------------------------------------------------------------|
| **Repository:**    | https://github.com/sabbelasichon/typo3-rector                |
| **Documentation:** | https://github.com/sabbelasichon/typo3-rector/tree/main/docs |
| **Packagist:**     | https://packagist.org/packages/ssch/typo3-rector             |

## Support

Please post questions in the TYPO3 Slack channel [#ext-typo3-rector](https://typo3.slack.com/archives/C019R5LAA6A)
or feel free to open an issue or start a discussion on GitHub.

## Credits

Many thanks to [Tomas Votruba](https://tomasvotruba.com) for his ongoing support and Rector.
Many thanks to every other contributor.

Oh, and if you've come down this far, you might as well follow me on [twitter](https://twitter.com/schreiberten).

## Known Drawbacks

### How to Apply Coding Standards?

Rector uses [nikic/php-parser](https://github.com/nikic/PHP-Parser/), built on technology called an
*abstract syntax tree* (AST). An AST doesn't know about spaces and when written to a file it produces poorly formatted
code in both PHP and docblock annotations. **That's why your project needs to have a coding standard tool** and a set of
formatting rules, so it can make Rector's output code nice and shiny again.

We're using [ECS](https://github.com/symplify/easy-coding-standard) with [this setup](ecs.php).
