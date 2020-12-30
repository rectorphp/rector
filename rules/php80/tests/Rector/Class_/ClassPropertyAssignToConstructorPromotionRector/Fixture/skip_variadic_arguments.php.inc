<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class SkipVariadicArguments
{
    private string $foo;
    /** @var string[] */
    private array $bars;

    public function __construct(
        string $foo,
        string ...$bars
    ) {
        $this->foo = $foo;
        $this->bars = $bars;
    }
}
?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class SkipVariadicArguments
{
    /** @var string[] */
    private array $bars;

    public function __construct(
        private string $foo,
        string ...$bars
    ) {
        $this->bars = $bars;
    }
}
?>
