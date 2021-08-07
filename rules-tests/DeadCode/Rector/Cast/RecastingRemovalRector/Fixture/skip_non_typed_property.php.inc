<?php

namespace Rector\Tests\DeadCode\Rector\Cast\RecastingRemovalRector\Rector;

class SkipNonTypedProperty
{
    /** @var int */
    public $property = 1;

    public function run()
    {
        $value = (int) $this->property;
    }
}

?>
