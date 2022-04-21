<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ClassStringTypeNoDefault
{
    /**
     * @var class-string
     */
    private $property;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ClassStringTypeNoDefault
{
    /**
     * @var class-string
     */
    private string $property;
}

?>
