<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

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
