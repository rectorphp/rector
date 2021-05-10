<?php

declare (strict_types=1);
namespace Rector\Order\Order;

final class OrderChangeAnalyzer
{
    /**
     * @param array<int, int> $oldToNewKeys
     */
    public function hasOrderChanged(array $oldToNewKeys) : bool
    {
        $keys = \array_keys($oldToNewKeys);
        $values = \array_values($oldToNewKeys);
        return $keys !== $values;
    }
}
