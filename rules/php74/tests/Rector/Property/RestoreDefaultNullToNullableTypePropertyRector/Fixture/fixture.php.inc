<?php

namespace Rector\Php74\Tests\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

class Fixture
{
    public ?string $name;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

class Fixture
{
    public ?string $name = null;
}

?>
