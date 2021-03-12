<?php

namespace Rector\Tests\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SkipNoDocblockAndDefaultValue
{
    private $full;

    public function isFull()
    {
        return $this->full;
    }
}

?>
