<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ArrayTypeFilledDefaultNull
{
    /**
     * @var array
     */
    private $property;

    public function __construct(array $property = null)
    {
        $this->property = $property;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ArrayTypeFilledDefaultNull
{
    private ?array $property;

    public function __construct(array $property = null)
    {
        $this->property = $property;
    }
}

?>
