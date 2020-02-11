<?php

namespace Rector\StrictCodeQuality\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

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

namespace Rector\StrictCodeQuality\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class SingleScalarAssert
{
    public function run($value)
    {
        assert(is_int($value));
        $value->call();
    }
}

?>
