<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

trait TraitA
{
    use TraitB;
}

trait TraitB
{
    public function foo()
    {
        echo $this->property;
    }
}

trait SkipTraitInTraitUser
{
    use TraitA;

    private $property = 10;
}
