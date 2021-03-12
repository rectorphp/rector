<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Source\MagicParent;

class SkipMagicParent extends MagicParent
{
    public function set()
    {
        $this->value = 5;
    }
}
