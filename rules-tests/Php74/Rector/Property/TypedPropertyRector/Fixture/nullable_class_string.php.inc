<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class NullableClassString
{
    /**
     * @var class-string|null
     */
    private $property;

    public function run(): ?string
    {
        return $this->property;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class NullableClassString
{
    /**
     * @var class-string|null
     */
    private ?string $property = null;

    public function run(): ?string
    {
        return $this->property;
    }
}

?>
