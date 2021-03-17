<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use DateTimeInterface;

class NullableType
{
    private ?DatetimeInterface $time;

    public function __construct(DatetimeInterface $time = null)
    {
        $this->time = $time;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use DateTimeInterface;

class NullableType
{
    public function __construct(private ?\DateTimeInterface $time = null)
    {
    }
}

?>
