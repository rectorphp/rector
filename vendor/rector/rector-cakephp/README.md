# Rector Rules for CakePHP

See available [CakePHP rules](/docs/rector_rules_overview.md)

## Install

```bash
composer require rector/rector-cakephp
```

## Use Sets

To add a set to your config, use `Rector\CakePHP\Set\CakePHPSetList` class and pick one of constants:

```php
use Rector\CakePHP\Set\CakePHPSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(CakePHPSetList::CAKEPHP_40);
};
```
