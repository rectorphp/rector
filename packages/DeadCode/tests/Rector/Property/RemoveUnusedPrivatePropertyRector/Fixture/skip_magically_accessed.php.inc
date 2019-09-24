<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipMagicallyAccessed
{
    private $seemsUnused;

    public function getValue($magicName)
    {
        // but it might be used :)
        return $this->$magicName;
    }
}
