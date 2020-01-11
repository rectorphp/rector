<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class SkipFalsePositive1
{
    public function indexBy(array $array, callable $key): array
    {
        $indexed = [];
        foreach ($array as $item) {
            $indexed[$key($item)] = $item;
        }

        return $indexed;
    }
}
