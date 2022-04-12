# How To Ignore Rule or Paths

## Preferred Way: Config

```php
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    // is there a file you need to skip?
    $rectorConfig->skip([
        // single file
        __DIR__ . '/src/ComplicatedFile.php',
        // or directory
        __DIR__ . '/src',
        // or fnmatch
        __DIR__ . '/src/*/Tests/*',

        // is there single rule you don't like from a set you use?
        SimplifyIfReturnBoolRector::class,

        // or just skip rule in specific directory
        SimplifyIfReturnBoolRector::class => [
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

For in-file exclusion, use `@noRector \Rector\SomeClass\NameRector` annotation:

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
