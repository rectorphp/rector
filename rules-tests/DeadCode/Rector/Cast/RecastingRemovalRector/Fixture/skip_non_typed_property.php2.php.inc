<?php

namespace Rector\Tests\DeadCode\Rector\Cast\RecastingRemovalRector\Rector;

class Config
{
    /** @var int */
    public $property = 1;
}

class SkipNonTypedProperty2
{
    public function run(Config $config)
    {
        $value = (int) $config->property;
    }
}

?>
