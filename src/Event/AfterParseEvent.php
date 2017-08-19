<?php declare(strict_types=1);

namespace Rector\Event;

use PhpParser\Node;
use Symfony\Component\EventDispatcher\Event;

final class AfterParseEvent extends Event
{
    /**
     * @param Node[] $nodes
     */
    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @param Node[] $nodes
     */
    public function changeNodes(array $nodes): void
    {
        $this->nodes = $nodes;
    }
}
