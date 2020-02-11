<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Source\MagicParent;

class SkipMagicParent extends MagicParent
{
    public function set()
    {
        $this->value = 5;
    }
}
