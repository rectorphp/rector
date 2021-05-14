# Rector Rules for Symfony

See available [Symfony rules](/docs/rector_rules_overview.md)

## Install

```bash
composer require rector/rector-symfony --dev
```

## Use Sets

To add a set to your config, use `Rector\Symfony\Set\SymfonySetList` class and pick one of constants:

```php
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SymfonySetList::SYMFONY_44);
};
```


### Symfony Container

To work with some Symfony rules, you now need to link your container XML file

```php
// rector.php
use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
        __DIR__ . '/var/cache/dev/AppKernelDevDebugContainer.xml'
    );
};
```
