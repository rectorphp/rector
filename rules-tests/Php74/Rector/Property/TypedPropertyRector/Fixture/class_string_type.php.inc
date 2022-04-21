<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ClassStringType
{
    /**
     * @var class-string
     */
    private $property = 'stdClass';
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ClassStringType
{
    /**
     * @var class-string
     */
    private string $property = 'stdClass';
}

?>
