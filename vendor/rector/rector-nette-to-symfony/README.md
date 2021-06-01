# Rector Nette to Symfony

Do you need to migrate from Nette to Symfony? You can ↓

- [How we Migrated 54 357-lines Application from Nette to Symfony in 2 People under 80 Hours](https://tomasvotruba.com/blog/2019/08/26/how-we-migrated-54-357-lines-of-code-nette-to-symfony-in-2-people-under-80-hours/)

3 part series in more depth:

- [How we Migrated from Nette to Symfony in 3 Weeks - Part 1](https://tomasvotruba.com/blog/2019/02/21/how-we-migrated-from-nette-to-symfony-in-3-weeks-part-1/)
- [Why we Migrated from Nette to Symfony in 3 Weeks - Part 2 - Escaping Semantic Hell](https://tomasvotruba.com/blog/2019/03/07/why-we-migrated-from-nette-to-symfony-in-3-weeks-part-2/)
- [Why we Migrated from Nette to Symfony in 3 Weeks - Part 3 - Brain Drain Dead Packages-Lock](https://tomasvotruba.com/blog/2019/03/11/why-we-migrated-from-nette-to-symfony-in-3-weeks-part-3/)

See available [rules](/docs/rector_rules_overview.md)

## Install

This package is already part of [rector/rector](http://github.com/rectorphp/rector) package, so it works out of the box.

All you need to do is install the main package, and you're good to go:

```bash
composer require rector/rector --dev
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

<br>

## Read a First Book About Rector

Are you curious, how Rector works internally, how to create your own rules and test them and why Rector was born? In May 2021 we've released the very first book: *Rector - The Power of Automated Refactoring*.

<a href="https://leanpub.com/rector-the-power-of-automated-refactoring">
<img src="https://github.com/rectorphp/the-power-of-automated-refactoring-feedback/raw/main/images/book_title.png">
</a>

By [buying a book](https://leanpub.com/rector-the-power-of-automated-refactoring) you directly support maintainers who are working on Rector.

<br>

## Support

Rector is a tool that [we develop](https://getrector.org/) and share for free, so anyone can automate their refactoring. But not everyone has dozens of hours to understand complexity of abstract-syntax-tree in their own time. **That's why we provide commercial support - to save your time**.

Would you like to apply Rector on your code base but don't have time for the struggle with your project? [Hire us](https://getrector.org/contact) to get there faster.
