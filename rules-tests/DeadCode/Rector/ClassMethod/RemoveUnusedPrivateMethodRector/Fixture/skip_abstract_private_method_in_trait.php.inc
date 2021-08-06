<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

trait TraitWithAbstractPrivate
{
    abstract private function privateMethod();

    public function publicMethod() {
        $this->privateMethod();
    }
}

class SkipAbstractPrivateMethodInTrait
{
    use TraitWithAbstractPrivate;

    private function privateMethod()
    {
        //code
    }
}
?>
