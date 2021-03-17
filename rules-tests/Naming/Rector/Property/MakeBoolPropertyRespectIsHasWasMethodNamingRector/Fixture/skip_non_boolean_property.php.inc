<?php

namespace Rector\Tests\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SkipNonBooleanProperty
{
    /**
     * @var string
     */
    public $full = 'string';

    public function isFull()
    {
        return $this->full;
    }
}

?>
