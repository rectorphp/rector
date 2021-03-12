<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class ScalarClass
{
    public function run($value)
    {
        /** @var int $value */
        $value->call();

        /** @var string $value */
        $value->call();

        /** @var bool $value */
        $value->call();
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class ScalarClass
{
    public function run($value)
    {
        assert(is_int($value));
        $value->call();
        assert(is_string($value));

        $value->call();
        assert(is_bool($value));

        $value->call();
    }
}

?>
