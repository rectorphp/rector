# Rector Rules for Laravel

See available [Laravel rules](/docs/rector_rules_overview.md)

## Install

```bash
composer require rector/rector-laravel --dev
```

## Use Sets

To add a set to your config, use `Rector\Laravel\Set\LaravelSetList` class and pick one of constants:

```php
use Rector\Laravel\Set\LaravelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(LaravelSetList::LARAVEL_60);
};
```
