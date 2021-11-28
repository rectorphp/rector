<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParamInRequiredAutowireRector\Fixture;

class WithRequiredAnnotation
{
    private $visibilityManipulator;

    /** @required */
    public function autowire(VisibilityManipulator $visibilityManipulator)
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
    public function autowire()
    {
    }
}

?>
