<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SkipAlreadyPrefixed
{
    /**
     * @var bool
     */
    private $isFull = false;

    public function isFull()
    {
        return $this->isFull;
    }
}

?>
