<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

final class VariadicConstructorParam
{
    /**
     * @var DateTime[]
     */
    private $dates = [];

    public function __construct(DateTime ...$dates) {
        $this->dates = $dates;
    }
}
?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

final class VariadicConstructorParam
{
    /**
     * @var DateTime[]
     */
    private array $dates = [];

    public function __construct(DateTime ...$dates) {
        $this->dates = $dates;
    }
}
?>
