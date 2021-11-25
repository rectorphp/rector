<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParamInRequiredAutowireRector\Fixture;

use Symfony\Contracts\Service\Attribute\Required;

class WithRequiredAttribute
{
    private $visibilityManipulator;

    #[Required]
    public function autowireSomeService(VisibilityManipulator $visibilityManipulator)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParamInRequiredAutowireRector\Fixture;

use Symfony\Contracts\Service\Attribute\Required;

class WithRequiredAttribute
{
    private $visibilityManipulator;

    #[Required]
    public function autowireSomeService()
    {
    }
}

?>
