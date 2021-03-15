<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipMagicallyAccessed
{
    private $seemsUnused;

    public function getValue($magicName)
    {
        // but it might be used :)
        return $this->$magicName;
    }
}
