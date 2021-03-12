<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class NullablePropertyScalar
{
    /**
     * @var int|null
     */
    private $countOrNull;

    public function countOrNull(): ?int
    {
        return $this->countOrNull;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class NullablePropertyScalar
{
    private ?int $countOrNull = null;

    public function countOrNull(): ?int
    {
        return $this->countOrNull;
    }
}

?>
