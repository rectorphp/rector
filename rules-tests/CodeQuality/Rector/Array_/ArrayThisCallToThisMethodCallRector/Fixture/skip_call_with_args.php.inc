<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

class SkipCallWithArgs
{
    public function run()
    {
        $values = [1, 5, 3];
        usort($values, [$this, 'compareSize']);
    }

    private function compareSize($first, $second)
    {
        return $first <=> $second;
    }
}
