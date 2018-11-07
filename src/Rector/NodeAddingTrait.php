<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use Rector\PhpParser\Node\Commander\NodeAddingCommander;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeAddingTrait
{
    /**
     * @var NodeAddingCommander
     */
    private $nodeAddingCommander;

    /**
     * @required
     */
    public function setNodeAddingCommander(NodeAddingCommander $nodeAddingCommander): void
    {
        $this->nodeAddingCommander = $nodeAddingCommander;
    }

    protected function addNodeAfterNode(Expr $node, Node $positionNode): void
    {
        $this->nodeAddingCommander->addNodeAfterNode($node, $positionNode);
    }
}
