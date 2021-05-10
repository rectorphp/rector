# Rector Rules for Doctrine

See available [Doctrine rules](/docs/rector_rules_overview.md)

## Install

```bash
composer require rector/rector-doctrine
```

## Use Sets

To add a set to your config, use `Rector\Doctrine\Set\DoctrineSetList` class and pick one of constants:

```php
use Rector\Doctrine\Set\DoctrineSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(DoctrineSetList::DOCTRINE_CODE_QUALITY);
};
```
