# Rector - Instant Upgrades and Automated Refactoring

[![Downloads](https://img.shields.io/packagist/dt/rector/rector.svg?style=flat-square)](https://packagist.org/packages/rector/rector)

<br>

Rector instantly upgrades and refactors the PHP code of your application.  It can help you in 2 major areas:

### 1. Instant Upgrades

Rector now supports upgrades from PHP 5.3 to 8.2 and major open-source projects like [Symfony](https://github.com/rectorphp/rector-symfony), [PHPUnit](https://github.com/rectorphp/rector-phpunit), and [Doctrine](https://github.com/rectorphp/rector-doctrine). Do you want to **be constantly on the latest PHP and Framework without effort**?

Use Rector to handle **instant upgrades** for you.

### 2. Automated Refactoring

Do you have code quality you need, but struggle to keep it with new developers in your team? Do you want to see smart code-reviews even when every senior developers sleeps?

Add Rector to your CI and let it **continuously refactor your code** and keep the code quality high.

Read our [blogpost](https://getrector.com/blog/new-setup-ci-command-to-let-rector-work-for-you) to see how to set up automated refactoring.

## Install

```bash
composer require rector/rector --dev
```

## Running Rector

There are 2 main ways to use Rector:

- a *single rule*, to have the change under control
- or group of rules called *sets*

To use them, create a `rector.php` in your root directory:

```bash
vendor/bin/rector
```

And modify it:

```php
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return static function (RectorConfig $rectorConfig): void {
    // register single rule
    $rectorConfig->rule(TypedPropertyFromStrictConstructorRector::class);

    // here we can define, what sets of rules will be applied
    // tip: use "SetList" class to autocomplete sets with your IDE
    $rectorConfig->sets([
        SetList::CODE_QUALITY
    ]);
};
```

Then dry run Rector:

```bash
vendor/bin/rector process src --dry-run
```

Rector will show you diff of files that it *would* change. To *make* the changes, drop `--dry-run`:

```bash
vendor/bin/rector process src
```

## Documentation

* Find [full documentation here](https://getrector.com/documentation/).
* [Explore Rector Rules](/docs/rector_rules_overview.md)

<br>

## Learn Faster with a Book

Are you curious, how Rector works internally, how to create your own rules and test them and why Rector was born?
Read [Rector - The Power of Automated Refactoring](https://leanpub.com/rector-the-power-of-automated-refactoring) that will take you step by step through the Rector setup and how to create your own rules.

<br>

## Empowered by Community :heart:

The Rector community is powerful thanks to active maintainers who take care of Rector sets for particular projects.

Among there projects belong:

* [palantirnet/drupal-rector](https://github.com/palantirnet/drupal-rector)
* [craftcms/rector](https://github.com/craftcms/rector)
* [FriendsOfShopware/shopware-rector](https://github.com/FriendsOfShopware/shopware-rector)
* [sabbelasichon/typo3-rector](https://github.com/sabbelasichon/typo3-rector)
* [sulu/sulu-rector](https://github.com/sulu/sulu-rector)
* [efabrica-team/rector-nette](https://github.com/efabrica-team/rector-nette)
* [Sylius/SyliusRector](https://github.com/Sylius/SyliusRector)
* [CoditoNet/rector-money](https://github.com/CoditoNet/rector-money)
* [laminas/laminas-servicemanager-migration](https://github.com/laminas/laminas-servicemanager-migration)
* [cakephp/upgrade](https://github.com/cakephp/upgrade)
* [driftingly/rector-laravel](https://github.com/driftingly/rector-laravel)

<br>

## Hire us to get Job Done :muscle:

Rector is a tool that [we develop](https://getrector.com/) and share for free, so anyone can automate their refactoring. But not everyone has dozens of hours to understand complexity of abstract-syntax-tree in their own time. **That's why we provide commercial support - to save your time**.

Would you like to apply Rector on your code base but don't have time for the struggle with your project? [Hire us](https://getrector.com/contact) to get there faster.

<br>

## How to Contribute

See [the contribution guide](/CONTRIBUTING.md) or go to development repository [rector/rector-src](https://github.com/rectorphp/rector-src).

<br>

## Debugging

You can use `--debug` option, that will print nested exceptions output:

```bash
vendor/bin/rector process src/Controller --dry-run --debug
```

Or with Xdebug:

1. Make sure [Xdebug](https://xdebug.org/) is installed and configured
2. Add `--xdebug` option when running Rector

```bash
vendor/bin/rector process src/Controller --dry-run --xdebug
```

To assist with simple debugging Rector provides 2 helpers to pretty-print AST-nodes:

```php
use PhpParser\Node\Scalar\String_;
$node = new String_('hello world!');

// prints node to string, as PHP code displays it
print_node($node);

// dump nested node object with nested properties
dump_node($node);

// 2nd argument is how deep the nesting is - this makes sure the dump is short and useful
dump_node($node, 1);
```

<br>

## Known Drawbacks

Rector uses [nikic/php-parser](https://github.com/nikic/PHP-Parser/), built on technology called an *abstract syntax tree* (AST). An AST doesn't know about spaces and when written to a file it produces poorly formatted code in both PHP and docblock annotations.

### How to Apply Coding Standards?

**Your project needs to have a coding standard tool** and a set of formatting rules, so it can make Rector's output code nice and shiny again.

We're using [ECS](https://github.com/symplify/easy-coding-standard) with [this setup](https://github.com/rectorphp/rector-src/blob/main/ecs.php).

### May cause unexpected output on File with mixed PHP+HTML content

When you apply changes to File(s) thas has mixed PHP+HTML content, you may need to manually verify the changed file after apply the changes.
