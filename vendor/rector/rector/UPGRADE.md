# How to Upgrade from Rector 0.9 to 0.10 (2021-03)

Use prepare Rector set to upgrade your code:

```bash
vendor/bin/rector process src --config vendor/rector/rector/upgrade/rector_010.php
```

Some changes have to be handled manually:

## In Symfony project, clear `config/bundles.php`

- drop `PhpConfigPrinterBundle` class

## Removed Attributes

- `getAttribute(AttributeKey::PARENT_CLASS_NAME)` → use `$scope->getClassReflection()` instead
- `getAttribute(AttributeKey::NAMESPACE_NAME)` → use `$scope->getNamespace()` instead
- `getAttribute(AttributeKey::NAMESPACE_NODE)` → use `$scope->getNamespace()` instead

<br>

# How to Upgrade From Rector 0.8 to 0.9 (2020-12)

## In Symfony project, clear `config/bundles.php`

- drop `ComposerJsonManipulatorBundle` class
- drop `ConsoleColorDiffBundle` class

## Set Consolidation

Sets with ambiguous naming were removed and rules moved to proper-named sets:

```diff
 use Rector\Core\Configuration\Option;
 use Rector\Set\ValueObject\SetList;
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $parameters = $containerConfigurator->parameters();

     $parameters->set(Option::SETS, [
-        SetList::SOLID,
+        SetList::CODING_STYLE,
-        SetList::PHPSTAN,
+        SetList::PRIVATIZATION,
     ]);
 };
```

## Single `SKIP` option `rector.php`

Since Rector 0.9 we switched from internal skipping to [`symplify/skipper` package](https://tomasvotruba.com/blog/2020/12/10/new-in-symplify-9-skipper-skipping-files-and-rules-made-simple/). Now there is only one `Option::SKIP` parameter to handle both paths and classes.

Replace deprecated `Option::EXCLUDE_RECTORS` parameters with `Option::SKIP`:

```diff
 use Rector\Core\Configuration\Option;
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $parameters = $containerConfigurator->parameters();

-    $parameters->set(Option::EXCLUDE_RECTORS, [
+    $parameters->set(Option::SKIP, [
         SomeRector::class,
     ]);
 };
```

Replace deprecated `Option::EXCLUDE_PATHS` parameters with `Option::SKIP`:

```diff
 use Rector\Core\Configuration\Option;
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $parameters = $containerConfigurator->parameters();

-    $parameters->set(Option::EXCLUDE_PATHS, [
+    $parameters->set(Option::SKIP, [
         __DIR__ . '/SomePath,
     ]);
 };
```

Be sure to have **exactly 1** `Option::SKIP` in the end, as the Symfony parameters are not merged, but overridden:

```diff
 use Rector\Core\Configuration\Option;
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $parameters = $containerConfigurator->parameters();

     $parameters->set(Option::SKIP, [
         SomeRector::class,
-    ]);
-
-    $parameters->set(Option::SKIP, [
         __DIR__ . '/SomePath,
     ]);
 };
```

## From CLI `--set`/`--level` to config

Rector now works more and more with set stacking. The number of sets is growing and IDE autocomplete helps to work with them effectively. If you use these options in CLI, move them to `rector.php` config like this:

```diff
-vendor/bin/rector process src --set php80
+vendor/bin/rector process src
```

```diff
 use Rector\Core\Configuration\Option;
+use Rector\Set\ValueObject\SetList;
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $parameters = $containerConfigurator->parameters();

     $parameters->set(Option::SETS, [
+        SetList::PHP_80,
     ]);
 };
```
