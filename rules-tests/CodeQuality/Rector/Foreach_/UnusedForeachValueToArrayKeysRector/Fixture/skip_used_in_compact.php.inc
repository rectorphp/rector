<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

class SkipUsedInCompact
{
    public function run()
    {
        foreach ($items as $field => $label) {
            $query = $this->find();
            $amount = $query->where([$this->aliasField($field) => $userId])->count();
            $items[$field] = compact('label', 'amount');
        }
    }
}