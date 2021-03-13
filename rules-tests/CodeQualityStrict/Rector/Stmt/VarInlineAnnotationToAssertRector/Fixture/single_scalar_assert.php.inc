<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

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

namespace Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class SingleScalarAssert
{
    public function run($value)
    {
        assert(is_int($value));
        $value->call();
    }
}

?>
