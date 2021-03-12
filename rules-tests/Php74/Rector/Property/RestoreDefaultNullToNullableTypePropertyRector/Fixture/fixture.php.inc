<?php

namespace Rector\Tests\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

class Fixture
{
    public ?string $name;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

class Fixture
{
    public ?string $name = null;
}

?>
