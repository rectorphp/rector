<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

/** @template T of \stdClass */
class TemplateType
{
    /** @param T $value */
    public function __construct(private \stdClass $value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\Fixture;

/** @template T of \stdClass */
class TemplateType
{
    /**
     * @var T
     */
    private \stdClass $value;
    /** @param T $value */
    public function __construct(\stdClass $value)
    {
        $this->value = $value;
    }
}

?>
