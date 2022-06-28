# Rector Rules for PHP Downgrade

See available [Downgrade rules](/docs/rector_rules_overview.md)

## Install

This package is already part of [rector/rector](http://github.com/rectorphp/rector) package, so it works out of the box.

All you need to do is install the main package, and you're good to go:

```bash
composer require rector/rector --dev
```

<br>

## Use Sets

To add a set to your config, use `Rector\Set\ValueObject\DowngradeLevelSetList` class and pick target set:

```php
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        DowngradeLevelSetList::DOWN_TO_PHP_72
    ]);
};
```

Then run Rector to downgrade your code to PHP 7.2!

```bash
vendor/bin/rector
```

<br>

## How the Downgrade Workflow looks?

It's simple in the nature. Read these *how-to* posts to get the idea:

* [How all Frameworks can Bump to PHP 8.1 and You can Keep Using Older PHP](https://getrector.org/blog/how-all-frameworks-can-bump-to-php-81-and-you-can-use-older-php)
* [Introducing ECS Prefixed and Downgraded to PHP 7.1](https://tomasvotruba.com/blog/introducing-ecs-prefixed-and-downgraded-to-php-71/)
* [How to bump Minimal PHP Version without Leaving Anyone Behind?](https://getrector.org/blog/how-to-bump-minimal-version-without-leaving-anyone-behind)
* [Rector 0.10 Released - with PHP 7.1 Support](https://getrector.org/blog/2021/03/22/rector-010-released-with-php71-support)

<br>

## Learn Rector Faster

Rector is a tool that [we develop](https://getrector.org/) and share for free, so anyone can save hundreds of hours on refactoring. But not everyone has time to understand Rector and AST complexity. You have 2 ways to speed this process up:

* read a book - <a href="https://leanpub.com/rector-the-power-of-automated-refactoring">The Power of Automated Refactoring</a>
* hire our experienced team to <a href="https://getrector.org/contact">improve your code base</a>

Both ways support us to and improve Rector in sustainable way by learning from practical projects.
