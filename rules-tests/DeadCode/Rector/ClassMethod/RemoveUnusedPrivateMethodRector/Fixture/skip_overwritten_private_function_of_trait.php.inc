<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

trait TraitWithNonAbstractPrivate
{
    private function privateMethod() {}

    public function publicMethod() {
        $this->privateMethod();
    }
}

class SkipOverwrittenPrivateFunctionOfTrait
{
    use TraitWithNonAbstractPrivate;

    private function privateMethod()
    {
        // overwriting private trait
    }
}
?>
