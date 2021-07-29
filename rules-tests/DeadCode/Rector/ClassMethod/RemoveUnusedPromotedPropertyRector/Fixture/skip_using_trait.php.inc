<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector\Fixture;

trait SomeTraitUsed
{
    public function foo()
    {
        echo $this->usedDependency;
    }
}

class SkipUsingTrait
{
    use SomeTraitUsed;

    public function __construct(
        private $usedDependency
    ) {
    }
}

?>
