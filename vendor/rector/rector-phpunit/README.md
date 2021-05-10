# Rector Rules for PHPUnit

See available [PHPUnit rules](/docs/rector_rules_overview.md)

## Install

```bash
composer require rector/rector-phpunit
```

## Use Sets

To add a set to your config, use `Rector\PHPUnit\Set\PHPUnitSetList` class and pick one of constants:

```php
use Rector\PHPUnit\Set\PHPUnitSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_90);
};
```
