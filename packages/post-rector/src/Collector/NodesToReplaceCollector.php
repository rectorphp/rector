<?php

declare(strict_types=1);

namespace Rector\PostRector\Collector;

use PhpParser\Node;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;

final class NodesToReplaceCollector implements NodeCollectorInterface
{
    /**
     * @todo use value object
     * @var Node[][]
     */
    private $nodesToReplace = [];

    public function addReplaceNodeWithAnotherNode(Node $node, Node $replaceWith): void
    {
        $this->nodesToReplace[] = [$node, $replaceWith];
    }

    public function isActive(): bool
    {
        return count($this->nodesToReplace) > 0;
    }

    public function getNodes()
    {
        return $this->nodesToReplace;
    }
}
