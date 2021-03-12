<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SkipPublicProperty
{
    /**
     * @var bool
     */
    public $full = false;

    public function isFull()
    {
        return $this->full;
    }
}

?>
