<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SkipConflict
{
    /**
     * @var bool
     */
    public $isFull;

    /**
     * @var bool
     */
    private $full = false;

    public function isFull()
    {
        return $this->full;
    }
}

?>
