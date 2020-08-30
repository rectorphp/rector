<?php

declare(strict_types=1);

namespace Rector\Order\Rector;

use Rector\Core\Rector\AbstractRector;
use Rector\Order\StmtOrder;
use Rector\Order\StmtVisibilitySorter;

abstract class AbstractConstantPropertyMethodOrderRector extends AbstractRector
{
    /**
     * @var StmtOrder
     */
    protected $stmtOrder;

    /**
     * @var StmtVisibilitySorter
     */
    protected $stmtVisibilitySorter;

    /**
     * @required
     */
    public function autowireAbstractConstantPropertyMethodOrderRector(
        StmtOrder $stmtOrder,
        StmtVisibilitySorter $stmtVisibilitySorter
    ): void {
        $this->stmtOrder = $stmtOrder;
        $this->stmtVisibilitySorter = $stmtVisibilitySorter;
    }

    /**
     * @param array<int, int> $oldToNewKeys
     */
    public function hasOrderChanged(array $oldToNewKeys): bool
    {
        return array_keys($oldToNewKeys) !== array_values($oldToNewKeys);
    }
}
