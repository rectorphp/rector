# How To Ignore Rule or Paths

## Prefered Way: Config

```php
<?php

// rector.php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // is there a file you need to skip?
    $parameters->set(Option::EXCLUDE_PATHS, [
        // single file
        __DIR__ . '/src/ComplicatedFile.php',
        // or directory
        __DIR__ . '/src',
        // or fnmatch
        __DIR__ . '/src/*/Tests/*',
    ]);

    // is there single rule you don't like from a set you use?
    $parameters->set(Option::EXCLUDE_RECTORS, [SimplifyIfReturnBoolRector::class]);

    // skip directory/file by rule
    $parameters->set(Option::SKIP, [
        Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector::class => [
            // single file
            __DIR__ . '/src/ComplicatedFile.php',
            // or directory
            __DIR__ . '/src',
            // or fnmatch
            __DIR__ . '/src/*/Tests/*',
        ],
    ]);
};
```

## In a File

For in-file exclusion, use `@noRector \FQN name` annotation:

```php
<?php

declare(strict_types=1);

class SomeClass
{
    /**
     * @noRector
     */
    public const NAME = '102';

    /**
     * @noRector
     */
    public function foo(): void
    {
        /** @noRector \Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector */
        round(1 + 0);
    }
}
```
