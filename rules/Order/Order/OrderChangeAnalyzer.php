<?php

declare(strict_types=1);

namespace Rector\Order\Order;

final class OrderChangeAnalyzer
{
    /**
     * @param array<int, int> $oldToNewKeys
     */
    public function hasOrderChanged(array $oldToNewKeys): bool
    {
        return array_keys($oldToNewKeys) !== array_values($oldToNewKeys);
    }
}
