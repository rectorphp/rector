<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

class MixedTypeInPhpDoc
{
    /** @param mixed $value */
    public function __construct(public $value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

class MixedTypeInPhpDoc
{
    /**
     * @var mixed
     */
    public $value;
    /** @param mixed $value */
    public function __construct($value)
    {
        $this->value = $value;
    }
}

?>
