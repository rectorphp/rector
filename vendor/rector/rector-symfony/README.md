# Rector Rules for Symfony

See available [Symfony rules](https://getrector.com/find-rule?activeRectorSetGroup=symfony)

## Install

This package is already part of [rector/rector](http://github.com/rectorphp/rector) package, so it works out of the box.

All you need to do is install the main package, and you're good to go:

```bash
composer require rector/rector --dev
```

## Use Sets

To add a set to your config, use `->withPreparedSets` method, and pick one :

```php
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPreparedSets(symfonyCodeQuality: true)
    ->withComposerBased(symfony: true);
```

If you're on PHP 7.x, you can use withSets() instead, for `symfonyCodeQuality` set, so you can define:

```php
use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withSets([
        SymfonySetList::SYMFONY_CODE_QUALITY,
    ]);
```

See [documentation](https://getrector.com/documentation/config-configuration#content-symfony-integration) for more.

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

<br>

## Learn Rector Faster

Rector is a tool that [we develop](https://getrector.org/) and share for free, so anyone can save hundreds of hours on refactoring. But not everyone has time to understand Rector and AST complexity. You have 2 ways to speed this process up:

* read a book - <a href="https://leanpub.com/rector-the-power-of-automated-refactoring">The Power of Automated Refactoring</a>
* hire our experienced team to <a href="https://getrector.org/contact">improve your code base</a>

Both ways support us to and improve Rector in sustainable way by learning from practical projects.
