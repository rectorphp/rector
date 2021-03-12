<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

use DateTimeInterface;

final class IterableInterfaceVarTag
{
    /**
     * @var iterable|DateTimeInterface[]
     */
    private $dates;

    /**
     * @param DateTimeInterface[] $dates
     */
    public function __construct(iterable $dates)
    {
        $this->dates = $dates;
    }
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

use DateTimeInterface;

final class IterableInterfaceVarTag
{
    /**
     * @var iterable|DateTimeInterface[]
     */
    private iterable $dates;

    /**
     * @param DateTimeInterface[] $dates
     */
    public function __construct(iterable $dates)
    {
        $this->dates = $dates;
    }
}
?>
