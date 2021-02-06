<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

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
