# Rector Rules for Symfony

See available [Symfony rules](https://getrector.com/find-rule?query=symfony+rules)

## Install

This package is already part of [rector/rector](http://github.com/rectorphp/rector) package, so it works out of the box.

All you need to do is install the main package, and you're good to go:

```bash
composer require rector/rector --dev
```

## Use Sets

To add a set to your config, use `Rector\Symfony\Set\SymfonySetList` class and pick one of constants:

```php

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withSymfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml')
    ->withSets([
        SymfonySetList::SYMFONY_62,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);
```

<br>

## Configuration

### Provide Symfony XML Service List

Some rules like `StringFormTypeToClassRector` need access to your Symfony container dumped XML. It contains list of form types with their string names, so it can convert them to class references.

How to add it? Check your `var/cache/` directory and find the XML file for your test env. Then add it in `rector.php`:

```php
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSymfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml');
```

That's it! Now you can run the `StringFormTypeToClassRector` and get your form classes converted safely.

---

### Provide Symfony PHP Container

Some rules like `AddRouteAnnotationRector` require additional access to your Symfony container. The rule takes container service "router" to load metadata about your routes.

```php
use Rector\Config\RectorConfig;
use Rector\Symfony\Bridge\Symfony\Routing\SymfonyRoutesProvider;
use Rector\Symfony\Configs\Rector\ClassMethod\AddRouteAnnotationRector;
use Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface;

return RectorConfig::configure()
    ->withSymfonyContainerPhp(__DIR__ . '/tests/symfony-container.php')
    ->registerService(SymfonyRoutesProvider::class, SymfonyRoutesProviderInterface::class);
```

The `tests/symfony-container.php` should provide your dependency injection container. The way you create the container is up to you. It can be as simple as:

```php
// tests/symfony-container.php

use App\Kernel;

require __DIR__ . '/bootstrap.php';

$appKernel = new Kernel('test', false);
$appKernel->boot();

return $appKernel->getContainer();
```

The version of your Symfony can be quite old. Public methods are stable from Symfony 2 to through 6 and the router have not changed much. The `AddRouteAnnotationRector` rule was tested and developed on Symfony 2.8 project.

---

Note: in this case, container cache PHP file located in `/var/cache/<env>/appProjectContainer.php` is not enough. Why? Few services require Kernel to be set, e.g. routes that are resolved in lazy way. This container file is only dumped without Kernel and [would crash with missing "kernel" error](https://github.com/symfony/symfony/issues/19840). That's why the rule needs full blown container.

<br>

## Learn Rector Faster

Rector is a tool that [we develop](https://getrector.org/) and share for free, so anyone can save hundreds of hours on refactoring. But not everyone has time to understand Rector and AST complexity. You have 2 ways to speed this process up:

* read a book - <a href="https://leanpub.com/rector-the-power-of-automated-refactoring">The Power of Automated Refactoring</a>
* hire our experienced team to <a href="https://getrector.org/contact">improve your code base</a>

Both ways support us to and improve Rector in sustainable way by learning from practical projects.
