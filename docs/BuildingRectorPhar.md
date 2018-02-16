# 3 Steps to Build PHAR

To build `rector.phar` just run:

```bash
composer build phar
```

That includes 3 steps:

## 1. Preffix Classes to Remove Conflicts

Making Rector classes and its dependencies unique prevents conflict issue [like this](https://github.com/rectorphp/rector/issues/178), when 2 classes of various versions are installed. E.g. when PHPStan requires `nikic/php-parser` `~3.0`, but rector uses `~4.0`.

That is job of [humbug/php-scoper](https://github.com/humbug/php-scoper):

```
Rector\Application => PHPScoper3gs48sg\Rector\Application
```

by running:

```php
vendor/bin/php-scoper add-prefix --output-dir=build --force
```

Do you look for **configuration**? See [scoper.inc.php](../scoper.inc.php).

All prefixed classes are in `/build` directory.

## 2. Rebuild Composer Dump to Reflect Prefixes

But who will tell the composer, that `Rector\Application` exists no more in `src/Application.php`?

Just rebuild the `vendor/autoload.php` file:

```bash
composer dump-autoload --working-dir=build --classmap-authoritative --no-dev
```

## 3. Pack to PHAR File

Second step is to pack all these unique classes to `retcor.phar` file. PHAR is like zip, just for PHP files and runable. When you call `rector.phar`, it will call `/bin/rector`.

That is job of [humbug/box](https://github.com/humbug/box):

```bash
vendor/bin/box build
```

Do you look for **configuration**? See [box.json](../box.json).

<br>

That's all!
