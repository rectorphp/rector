<?php

namespace Rector\Tests\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SkipUnknownPrefix
{
    /**
     * @var bool
     */
    private $full = false;

    public function unknownFull()
    {
        return $this->full;
    }
}

?>
