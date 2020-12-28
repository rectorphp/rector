<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class SingleScalarAssert
{
    public function run($value)
    {
        /** @var int $value */
        $value->call();
    }
}

?>
-----
<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class SingleScalarAssert
{
    public function run($value)
    {
        assert(is_int($value));
        $value->call();
    }
}

?>
