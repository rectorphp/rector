<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SkipNoDocblockAndDefaultValue
{
    private $full;

    public function isFull()
    {
        return $this->full;
    }
}

?>
