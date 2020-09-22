<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SkipMultiplePrefixedMethods
{
    /**
     * @var bool
     */
    private $full = false;

    public function isFull()
    {
        return $this->full;
    }

    public function wasFull()
    {
        return $this->full;
    }
}

?>
