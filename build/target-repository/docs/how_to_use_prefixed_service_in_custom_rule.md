# How to Use Prefixed Service In Custom Rule

Since `rector/rector` version 0.11, it is not possible to use service that previously not prefixed in previous version, for example, you have the following custom rector rule with `Symplify\PackageBuilder\Strings\StringFormatConverter` dependency:

```php
use Symplify\PackageBuilder\Strings\StringFormatConverter;

final class UnderscoreToCamelCaseVariableNameRector extends AbstractRector
{
    public function __construct(StringFormatConverter $stringFormatter)
    {
        // ...
    }
}
```

For above example, the `Symplify\PackageBuilder\Strings\StringFormatConverter` is no longer exists, you can consume via require --dev it:

```bash
composer require --dev symplify/package-builder
```

After that, you need to register the `symplify/package-builder`'s src to service in `rector.php`:

```php
<?php
// rector.php

use Symplify\PackageBuilder\Strings\StringFormatConverter;

return static function (ContainerConfigurator $containerConfigurator): void {
    // ...

	$services = $containerConfigurator->services();
	$services->set(StringFormatConverter::class);

    // ...
};
```

Now, the `Symplify\PackageBuilder\Strings\StringFormatConverter` service will be detected again.