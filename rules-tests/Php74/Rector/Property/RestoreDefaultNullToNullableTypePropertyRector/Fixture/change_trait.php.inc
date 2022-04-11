<?php

namespace Rector\Tests\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

trait ChangeTrait
{
    public ?string $name;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

trait ChangeTrait
{
    public ?string $name = null;
}

?>
