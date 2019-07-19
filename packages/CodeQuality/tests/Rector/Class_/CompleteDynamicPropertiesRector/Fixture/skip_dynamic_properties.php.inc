<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class SkipDynamicProperties
{
    public function unserialize($serialized)
    {
        $data = \unserialize($serialized);
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }
}
