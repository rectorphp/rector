# Rector Rules for Nette

See available [Nette rules](/docs/rector_rules_overview.md)

## Install

```bash
composer require rector/rector-nette
```

## Use Sets

To add a set to your config, use `Rector\Nette\Set\NetteSetList` class and pick one of constants:

```php
use Rector\Nette\Set\NetteSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(NetteSetList::NETTE_24);
};
```
