# How To Register Custom SetList Constant

You can have custom `SetList` class that has constants that pointed to your own config, for example:


```php
<?php

namespace App\Set\ValueObject;

use Rector\Set\Contract\SetListInterface;

class SetList implements SetListInterface
{
    public const MY_FRAMEWORK_20 = __DIR__ . '/../../../config/set/my-framework-20.php';
}
```

Now, you can register your custom `SetList`'s constant via import from `$containerConfigurator`, for example:


```php
<?php
// rector.php
declare(strict_types=1);

use Rector\Core\Configuration\Option;
use App\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::MY_FRAMEWORK_20);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/src']);
};
```

Note that if you are looking for the downgrade categories, there is already the `DowngradeSetList`:

```php
<?php
// rector.php
declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\DowngradeSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(DowngradeSetList::PHP_70)

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/src']);
};
```
