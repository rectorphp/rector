<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

trait SkipInTrait
{
    public function run(array $values)
    {
        usort($values, [$this, 'compareSize']);

        return $values;
    }

    private function compareSize(int $first, $second): bool
    {
        return $first <=> $second;
    }
}
