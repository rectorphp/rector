<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class NullableClassStringOnFillByNonConstruct
{
    /**
     * @var class-string
     */
    private $property;

    public function fill()
    {
        $this->property = 'stdClass';
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class NullableClassStringOnFillByNonConstruct
{
    /**
     * @var class-string
     */
    private ?string $property = null;

    public function fill()
    {
        $this->property = 'stdClass';
    }
}

?>
