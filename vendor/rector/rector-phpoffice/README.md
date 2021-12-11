# Rector Rules for PHP Office

See available [PHP Office rules](/docs/rector_rules_overview.md)

## Install

This package is already part of [rector/rector](http://github.com/rectorphp/rector) package, so it works out of the box.

All you need to do is install the main package, and you're good to go:

```bash
composer require rector/rector --dev
```

## Use Sets

To add a set to your config, use `Rector\PHPOffice\Set\PHPOfficeSetList` class and pick one of constants:

```php
use Rector\PHPOffice\Set\PHPOfficeSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(PHPOfficeSetList::PHPEXCEL_TO_PHPSPREADSHEET);
};
```

<br>

## Read a First Book About Rector

Are you curious, how Rector works internally, how to create your own rules and test them and why Rector was born? In May 2021 we've released the very first book: *Rector - The Power of Automated Refactoring*.

<a href="https://leanpub.com/rector-the-power-of-automated-refactoring">
<img src="https://github.com/rectorphp/the-power-of-automated-refactoring-feedback/raw/main/images/book_title.png">
</a>

By [buying a book](https://leanpub.com/rector-the-power-of-automated-refactoring) you directly support maintainers who are working on Rector.

<br>

## Support

Rector is a tool that [we develop](https://getrector.org/) and share for free, so anyone can automate their refactoring. But not everyone has dozens of hours to understand complexity of abstract-syntax-tree in their own time. **That's why we provide commercial support - to save your time**.

Would you like to apply Rector on your code base but don't have time for the struggle with your project? [Hire us](https://getrector.org/contact) to get there faster.
