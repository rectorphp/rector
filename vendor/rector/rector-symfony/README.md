# Rector Rules for Symfony

See available [Symfony rules](/docs/rector_rules_overview.md)

## Install

This package is already part of [rector/rector](http://github.com/rectorphp/rector) package, so it works out of the box.

All you need to do is install the main package, and you're good to go:

```bash
composer require rector/rector --dev
```

## Use Sets

To add a set to your config, use `Rector\Symfony\Set\SymfonySetList` class and pick one of constants:

```php
use Rector\Symfony\Set\SymfonySetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->symfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml');

    $rectorConfig->sets([
        SymfonySetList::SYMFONY_52,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);
};
```

<br>

## Configuration

Some rules like `StringFormTypeToClassRector` need access to your Symfony container dumped XML. It contains list of form types with their string names, so it can convert them to class references.

How to add it? Check your `/var/cache` directory and find the XML file for your test env. Then add it in `rector.php`:

```php
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->symfonyContainerXml(__DIR__ . '/var/cache/<env>/appProjectContainer.xml');
};
```

That's it! Now you can run the `StringFormTypeToClassRector` and get your form classes converted safely.

---

Some rules like `AddRouteAnnotationRector` require additional access to your Symfony container. The rule takes container service "router" to load metadata about your routes.

```php
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->symfonyContainerPhp(__DIR__ . '/tests/symfony-container.php');
};
```

The `tests/symfony-container.php` should provide your dependency injection container. The way you create the container is up to you. It can be as simple as:

```php
// tests/symfony-container.php

$appKernel = new AppKernel('tests', false);
$appKernel->boot();

return $appKernel->getContainer();
```

The version of your Symfony can be quite old. Public methods are stable from Symfony 2 to through 6 and the router have not changed much. The `AddRouteAnnotationRector` rule was tested and developed on Symfony 2.8 project.

---

Note: in this case, container cache PHP file located in `/var/cache/<env>/appProjectContainer.php` is not enough. Why? Few services require Kernel to be set, e.g. routes that are resolved in lazy way. This container file is only dumped without Kernel and [would crash with missing "kernel" error](https://github.com/symfony/symfony/issues/19840). That's why the rule needs full blown container.


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
