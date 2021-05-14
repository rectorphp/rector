# Rector Nette to Symfony

Do you need to migrate from Nette to Symfony? You can ↓

- [How we Migrated 54 357-lines Application from Nette to Symfony in 2 People under 80 Hours](https://tomasvotruba.com/blog/2019/08/26/how-we-migrated-54-357-lines-of-code-nette-to-symfony-in-2-people-under-80-hours/)

3 part series in more depth:

- [How we Migrated from Nette to Symfony in 3 Weeks - Part 1](https://tomasvotruba.com/blog/2019/02/21/how-we-migrated-from-nette-to-symfony-in-3-weeks-part-1/)
- [Why we Migrated from Nette to Symfony in 3 Weeks - Part 2 - Escaping Semantic Hell](https://tomasvotruba.com/blog/2019/03/07/why-we-migrated-from-nette-to-symfony-in-3-weeks-part-2/)
- [Why we Migrated from Nette to Symfony in 3 Weeks - Part 3 - Brain Drain Dead Packages-Lock](https://tomasvotruba.com/blog/2019/03/11/why-we-migrated-from-nette-to-symfony-in-3-weeks-part-3/)

See available [rules](/docs/rector_rules_overview.md)

## Install

```bash
composer require rector/rector-nette-to-symfony
```

## Use Sets

To add a set to your config, use `Rector\Symfony\Set\SymfonySetList` class and pick one of constants:

```php
use Rector\NetteToSymfony\Set\NetteToSymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(NetteToSymfonySetList::NETTE_TO_SYMFONY);
};
```
