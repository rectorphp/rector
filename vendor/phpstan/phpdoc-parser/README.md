<h1 align="center">PHPDoc-Parser for PHPStan</h1>

<p align="center">
	<a href="https://github.com/phpstan/phpdoc-parser/actions"><img src="https://github.com/phpstan/phpdoc-parser/workflows/Build/badge.svg" alt="Build Status"></a>
	<a href="https://packagist.org/packages/phpstan/phpdoc-parser"><img src="https://poser.pugx.org/phpstan/phpdoc-parser/v/stable" alt="Latest Stable Version"></a>
	<a href="https://choosealicense.com/licenses/mit/"><img src="https://poser.pugx.org/phpstan/phpstan/license" alt="License"></a>
	<a href="https://phpstan.org/"><img src="https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat" alt="PHPStan Enabled"></a>
</p>

* [PHPStan](https://phpstan.org)

------

Next generation phpDoc parser with support for intersection types and generics.

## Code of Conduct

This project adheres to a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project and its community, you are expected to uphold this code.

## Building

Initially you need to run `composer install`, or `composer update` in case you aren't working in a folder which was built before.

Afterwards you can either run the whole build including linting and coding standards using

    vendor/bin/phing
    
or run only tests using

    vendor/bin/phing tests
