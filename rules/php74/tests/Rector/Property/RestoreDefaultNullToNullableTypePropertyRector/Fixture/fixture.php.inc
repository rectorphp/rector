<?php

namespace Rector\Php74\Tests\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

class SomeClass
{
    public ?string $name;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

class SomeClass
{
    public ?string $name = null;
}

?>
