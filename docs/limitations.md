# Current Limitations and Installation Alternatives

At least until [#3490](https://github.com/rectorphp/rector/issues/3490) is resolved, Rector relies on _runtime PHP reflection_ to analyze your source code. That means the code Rector works on cannot merely be treated as _data_, but has to be loaded (through the autoloader) into memory to be analyzed by the PHP interpreter.

> Read the [PHPStan 0.12.26 release announcement](https://phpstan.org/blog/zero-config-analysis-with-static-reflection) to learn more about runtime and static reflection. PHPStan is the static analysis tool used by Rector.

This brings a few limitations and affects your choices of how you can run Rector.

## PHP Language Level

The PHP process that runs Rector code also needs to load and parse your source code. So, you need to run a version of PHP that is both compatible with your project code as well as Rector.

This means you cannot run Rector on PHP 8 to work on a project that uses language features limited to previous PHP versions.

> One example for this is [array and string offset access using curly braces](https://www.php.net/manual/de/migration74.deprecated.php#migration74.deprecated.core.array-string-access-curly-brace).

But also the other way round, you cannot run Rector on PHP 7 to analyze a project with PHP 8 language features, like [constructor property promotion](https://stitcher.io/blog/constructor-promotion-in-php-8).

Together with Rector's minimum PHP 7.3 version requirement, this might limit your options of how to run Rector.

Note that using an older version of Rector might allow you to run a version of PHP <7.3.

## Code Collisions

Since Rector needs to load the code it works on in addition to the code it needs to run itself, conflicts may occur.

One practical example of this is when you're running Rector on a project that uses an older version of Symfony. Since Rector uses a few Symfony Components itself, you may either run into problems when classes are re-declared, or when Rector happens to load and later (by accident) run parts of Symfony from your project installation. In the later case, the mix-up of different versions may lead to generally unpredictable results.

To remedy this situation, [Rector Prefixed](https://github.com/rectorphp/rector-prefixed) has been created. Basically, it takes Rector and all of its dependencies and moves _all_ that code under a unique namespace prefix.

(Add a note here: Drawbacks? Why isn't Rector-prefixed _the_ recommended approach?)

## Installation Options

With these limitations in mind, here are your installation options.

### 1. Direct Composer install

```bash
composer require rector/rector --dev
```

Composer will try to find a version of Rector compatible with your current version of PHP. Also, since it will resolve a _single_ version for every dependency your project and Rector have in common, code collisions will not occur.

The downside is that having Rector installed this way might affect the dependency versions your project will be able to use.

### 2. Use Composer to install Rector Prefixed

```bash
composer require rector/rector-prefixed --dev
```

Since Rector Prefixed brings along most of the code it needs in a separate namespace, the impact and resulting limitations for your project's requirements are minimal.

As of writing, the only additional dependency requirement is on `phpstan/phpstan`. But apart from that, Composer should be able to install it with whatever packages your project requires.

If you need Rector `^0.9.0`, however, you need to run at least PHP 7.3.

### 3. Use Docker to run Rector

When you're using a different version of PHP than the desired version of Rector supports, you can run one of the Docker images we provide.

See the [documentation on running in Docker](/docs/how_to_run_rector_in_docker.md) on how to do this.

There is one caveat, though: As of writing, the [official Rector Docker images](https://hub.docker.com/r/rector/rector/tags?page=1&ordering=last_updated) are built with PHP 8.0 and use the non-prefixed version of Rector.

So, you might run into problems with these images when your code uses language features not compatible with PHP 8, or when there is a collision between your project's and Rector's dependencies.

[Work is underway](https://github.com/rectorphp/rector/pull/5667) to improve this situation and to provide Docker images with alternative PHP versions and/or a prefixed version of Rector.
