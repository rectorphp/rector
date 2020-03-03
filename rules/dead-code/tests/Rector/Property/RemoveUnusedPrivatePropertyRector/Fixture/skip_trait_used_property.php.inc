<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipTraitUsedProperty
{
    use PropertyUser;

    private $usedProperty = 10;
}

trait PropertyUser
{
    public function run()
    {
        return $this->usedProperty;
    }
}
