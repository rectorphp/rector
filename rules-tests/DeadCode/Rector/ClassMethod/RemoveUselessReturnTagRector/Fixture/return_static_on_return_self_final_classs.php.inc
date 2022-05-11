<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class ReturnStaticOnReturnSelfFinalClass
{
    private static self $property;

    /**
     * @return static
     */
    public static function resolve(): self
    {
        return self::$property ??= new static();
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class ReturnStaticOnReturnSelfFinalClass
{
    private static self $property;

    public static function resolve(): self
    {
        return self::$property ??= new static();
    }
}

?>
