<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class AssignFreshVar
{
    public function run($value)
    {
        /** @var int $int */
        $int = $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class AssignFreshVar
{
    public function run($value)
    {
        /** @var int $int */
        $int = $value;
        assert(is_int($int));
    }
}

?>
