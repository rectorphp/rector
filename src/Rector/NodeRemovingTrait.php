<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use Rector\PhpParser\Node\Commander\NodeRemovingCommander;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeRemovingTrait
{
    /**
     * @var NodeRemovingCommander
     */
    private $nodeRemovingCommander;

    /**
     * @required
     */
    public function setNodeRemovingCommander(NodeRemovingCommander $nodeRemovingCommander): void
    {
        $this->nodeRemovingCommander = $nodeRemovingCommander;
    }

    protected function removeNode(Node $node): void
    {
        $this->nodeRemovingCommander->addNode($node);

        $this->appliedRectorCollector->addRectorClass(static::class);
    }
}
q