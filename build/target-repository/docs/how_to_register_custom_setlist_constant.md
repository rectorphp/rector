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
use App\Set\ValueObject\SetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([SetList::MY_FRAMEWORK_20]);

    $rectorConfig->paths([__DIR__ . '/src']);
};
```

Note that if you are looking for the downgrade categories, there is already the `DowngradeSetList`:

```php
use Rector\Set\ValueObject\DowngradeSetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([DowngradeSetList::PHP_70])

    $rectorConfig->paths([__DIR__ . '/src']);
};
```
