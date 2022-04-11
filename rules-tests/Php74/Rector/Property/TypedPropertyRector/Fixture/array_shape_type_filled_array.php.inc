<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ArrayShapeTypeFilledArray
{
    /**
     * @var array{name: string}
     */
    private $property;

    public function __construct(array $property = [])
    {
        $this->property = $property;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ArrayShapeTypeFilledArray
{
    /**
     * @var array{name: string}
     */
    private array $property;

    public function __construct(array $property = [])
    {
        $this->property = $property;
    }
}

?>
