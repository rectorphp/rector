# How To Configure Rules

Every rector can have its own configuration. E.g. the `DowngradeTypedPropertyRector` rule will add a docblock or not depending on its property `ADD_DOC_BLOCK`:

```php
<?php

// rector.php

declare(strict_types=1);

use Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // Don't output the docBlocks when removing typed properties
    $services->set(DowngradeTypedPropertyRector::class)
        ->call('configure', [[
            DowngradeTypedPropertyRector::ADD_DOC_BLOCK => false,
        ]]);
};
```
