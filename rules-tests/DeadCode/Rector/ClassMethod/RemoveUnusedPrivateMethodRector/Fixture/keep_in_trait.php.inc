<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class KeepInTrait
{
    use RandomTraitWithPrivateMethod;

    public function go()
    {
        $this->hide();
    }
}

trait RandomTraitWithPrivateMethod
{
    private function hide()
    {

    }
}
