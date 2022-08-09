# Rector Rules for Nette

See available [Nette rules](/docs/rector_rules_overview.md)

## Install

This package is a Rector extension developed by community around Nette. To add it, install package as dependency:

```bash
composer require <todo>/rector-nette --dev
```

## Use Sets

To add a set to your config, use `Rector\Nette\Set\NetteSetList` class and pick one of constants:

```php
use Rector\Nette\Set\NetteSetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        NetteSetList::NETTE_24,
    ]);
};
```

<br>

## Learn Rector Faster

Rector is a tool that [we develop](https://getrector.org/) and share for free, so anyone can save hundreds of hours on refactoring. But not everyone has time to understand Rector and AST complexity. You have 2 ways to speed this process up:

* read a book - <a href="https://leanpub.com/rector-the-power-of-automated-refactoring">The Power of Automated Refactoring</a>
* hire our experienced team to <a href="https://getrector.org/contact">improve your code base</a>

Both ways support us to and improve Rector in sustainable way by learning from practical projects.
