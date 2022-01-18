# Static Reflection and Autoload


Rector is using static reflection to load code without running it since version 0.10. That means your classes are found **without composer autoload and without running them**. Rector will find them and work with them as you have PSR-4 autoload properly setup. This comes very useful in legacy projects or projects with custom autoload.

Do you want to know more about it? Continue here:

- [From Doctrine Annotations Parser to Static Reflection](https://getrector.org/blog/from-doctrine-annotations-parser-to-static-reflection)
- [Legacy Refactoring made Easy with Static Reflection](https://getrector.org/blog/2021/03/15/legacy-refactoring-made-easy-with-static-reflection)
- [Zero Config Analysis with Static Reflection](https://phpstan.org/blog/zero-config-analysis-with-static-reflection) - from PHPStan

```php
// rector.php

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // Rector is using static reflection to load code without running it - see https://phpstan.org/blog/zero-config-analysis-with-static-reflection
    $parameters->set(Option::AUTOLOAD_PATHS, [
        // discover specific file
        __DIR__ . '/file-with-functions.php',
        // or full directory
        __DIR__ . '/project-without-composer',
    ]);
```

## Include Files

Do you need to include constants, class aliases or custom autoloader? Use `BOOTSTRAP_FILES` parameter:

```php
// rector.php

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::BOOTSTRAP_FILES, [
        __DIR__ . '/constants.php',
        __DIR__ . '/project/special/autoload.php',
    ]);
};
```

Listed files will be executed like:

```php
include $filePath;
```

## Troubleshooting

Sometime, when we run Rector to class that detect children class, like `\Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector`, we may experience that parent class changed to final while it has children class, it because of the `PHPStan\Reflection\ReflectionProvider` cannot get all classes on scanning it on usage via `FamilyRelationsAnalyzer` service.

To avoid this issue, you may dump all classes via composer:

```bash
composer dump-autoload -o
```

before run the rector.

If the false positive still happen, you can skip the rule applied as last resort to do:

```php
    $parameters->set(Option::SKIP, [
        \Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector::class => [
            // classes that has children, and not detected even with composer dump-autoload -o
            __DIR__ . '/src/HasChildClass.php',
        ],
    ]);
```

## Dealing with "Class ... was not found while trying to analyse it..."
Sometimes you may encounter this error ([see here for an example](https://github.com/rectorphp/rector/issues/6688)) even if the class is there and it seems to work properly with other tools (e.g. PHPStan).

In this case you may want to try one of the following solutions:

### Register to `Option::AUTOLOAD_PATHS`:

```php
    $parameters->set(Option::AUTOLOAD_PATHS, [
        // the path to the exact class file
        __DIR__ . '/vendor/acme/my-custom-dependency/src/Your/Own/Namespace/TheAffectedClass.php',
        // or you can specify a wider scope
        __DIR__ . '/vendor/acme/my-custom-dependency/src',
        // WARNING: beware of performances, try to narrow down the path
        //          as much as you can or you will slow down each run
    ]);
```

### Register the path of the class to composer.json's `"files"` config, eg:

```javascript
    "autoload-dev": {
        "files": [
            "vendor/acme/my-custom-dependency/src/Your/Own/Namespace/TheAffectedClass.php"
        ]
    }
```

After that, run:

```bash
composer dump-autoload
```

and re-run the rector.
