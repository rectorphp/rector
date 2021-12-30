<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

class ArrayShapes
{
    /** @param array<string, array{\stdClass, \Exception}> $value */
    public function __construct(private array $value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

class ArrayShapes
{
    /**
     * @var array<string, array{\stdClass, \Exception}>
     */
    private array $value;
    /** @param array<string, array{\stdClass, \Exception}> $value */
    public function __construct(array $value)
    {
        $this->value = $value;
    }
}

?>
