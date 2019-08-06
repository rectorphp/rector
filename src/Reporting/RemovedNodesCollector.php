<?php declare(strict_types=1);

namespace Rector\Reporting;

use PhpParser\Node;

final class RemovedNodesCollector
{
    /**
     * @var Node[]
     */
    private $removedNodes = [];

    public function collect(Node $node): void
    {
        $this->removedNodes[] = $node;
    }

    public function getCount(): int
    {
        return count($this->removedNodes);
    }
}
