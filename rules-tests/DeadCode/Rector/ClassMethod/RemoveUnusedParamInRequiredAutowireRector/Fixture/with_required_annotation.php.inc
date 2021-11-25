<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParamInRequiredAutowireRector\Fixture;

class WithRequiredAnnotation
{
    private $visibilityManipulator;

    /** @required */
    public function autowireSomeService(VisibilityManipulator $visibilityManipulator)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParamInRequiredAutowireRector\Fixture;

class WithRequiredAnnotation
{
    private $visibilityManipulator;

    /** @required */
    public function autowireSomeService()
    {
    }
}

?>
