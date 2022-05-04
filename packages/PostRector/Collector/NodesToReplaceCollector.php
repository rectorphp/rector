<?php

declare(strict_types=1);

namespace Rector\PostRector\Collector;

use PhpParser\Node;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;

/**
 * @deprecated Resolve in the rules itself instead of hacking around collector.
 */
final class NodesToReplaceCollector implements NodeCollectorInterface
{
    /**
     * @var array<array{Node, Node}>
     */
    private array $nodesToReplace = [];

    public function addReplaceNodeWithAnotherNode(Node $node, Node $replaceWith): void
    {
        $this->nodesToReplace[] = [$node, $replaceWith];
    }

    public function isActive(): bool
    {
        return $this->nodesToReplace !== [];
    }

    /**
     * @return array<array{Node, Node}>
     */
    public function getNodes(): array
    {
        return $this->nodesToReplace;
    }
}
