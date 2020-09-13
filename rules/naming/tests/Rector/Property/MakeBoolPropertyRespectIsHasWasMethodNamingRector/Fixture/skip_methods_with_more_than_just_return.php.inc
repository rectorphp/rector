<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;


class SkipMethodsWithMoreThanJustReturn
{
    /**
     * @var bool
     */
    private $full = false;

    /**
     * @var bool
     */
    public $property = true;

    public function isFull()
    {
        if ($this->property === true) {
            return true;
        }

        return $this->full;
    }
}

?>
