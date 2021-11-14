<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\Intersection\FirstTypeInterface;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\Intersection\SecondTypeInterface;

final class IncludeIntersectionType
{
    private $firstAndSecond;

    public function __construct(FirstTypeInterface&SecondTypeInterface $firstAndSecond)
    {
        $this->firstAndSecond = $firstAndSecond;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\Intersection\FirstTypeInterface;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\Intersection\SecondTypeInterface;

final class IncludeIntersectionType
{
    private \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\Intersection\FirstTypeInterface&\Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\Intersection\SecondTypeInterface $firstAndSecond;

    public function __construct(FirstTypeInterface&SecondTypeInterface $firstAndSecond)
    {
        $this->firstAndSecond = $firstAndSecond;
    }
}

?>
