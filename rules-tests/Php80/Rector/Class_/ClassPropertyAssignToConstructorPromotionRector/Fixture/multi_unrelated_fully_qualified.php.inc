<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class MultiUnrelatedFullyQualified
{
    public \DateTime $x;
    public \stdClass $y;

    public function __construct(
        \DateTime $x,
        \stdClass $y
    ) {
        $this->x = $x;
        $this->y = $y;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class MultiUnrelatedFullyQualified
{
    public function __construct(public \DateTime $x, public \stdClass $y)
    {
    }
}

?>
