<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class SomeClass
{
    public function run($value)
    {
        /** @var SpecificClass $value */
        $value->call();
    }
}

class SpecificClass
{
}

?>
-----
<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class SomeClass
{
    public function run($value)
    {
        assert($value instanceof \Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture\SpecificClass);
        $value->call();
    }
}

class SpecificClass
{
}

?>
