<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipMagicallyAccessedFetch
{
    private $seemsUnused;

    private $anotherName = 'asdf';

    public function getValue()
    {
        // but it might be used :)
        return $this->{$this->anotherName};
    }
}
