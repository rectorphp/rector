# How To Configure Rules

Rector rules that implement `Rector\Core\Contract\Rector\ConfigurableRectorInterface` can be configured.

Typical example is `Rector\Renaming\Rector\Name\RenameClassRector`:

```php
<?php

// rector.php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameClassRector::class)
        ->configure([
            'App\SomeOldClass' => 'App\SomeNewClass',
        ]);
};
```
