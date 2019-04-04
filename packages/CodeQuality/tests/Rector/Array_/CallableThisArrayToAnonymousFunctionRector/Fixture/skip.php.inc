<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

class Skip
{
    public function run(array $values)
    {
        usort($values, [$this, 'compareSizeA']);
        usort($values, [$that, 'compareSize']);

        return $values;
    }

    private function compareSize(int $first, $second): bool
    {
        return $first <=> $second;
    }
}
