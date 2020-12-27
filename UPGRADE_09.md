# How to Upgrade From Rector 0.8 to 0.9

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
-        Setlist::SOLID,
+        SetList::CODING_STYLE,
-        Setlist::PHPSTAN,
+        Setlist::PRIVATIZATION,
     ]);
 };
```

## `rector.php`

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
