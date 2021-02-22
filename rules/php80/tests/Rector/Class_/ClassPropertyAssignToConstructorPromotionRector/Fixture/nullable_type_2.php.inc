<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class NullableType2
{
    private ?string $str;

    public function __construct(string $str = null)
    {
        $this->str = $str;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class NullableType2
{
    public function __construct(private ?string $str = null)
    {
    }
}

?>
