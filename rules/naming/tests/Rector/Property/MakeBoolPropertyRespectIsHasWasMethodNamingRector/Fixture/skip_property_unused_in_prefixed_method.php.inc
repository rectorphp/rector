<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SkipPropertyUnusedInPrefixedMethod
{
    /**
     * @var bool
     */
    private $full = false;

    public function isFull()
    {
        return true;
    }

    public function something()
    {
        return $this->full;
    }
}

?>
