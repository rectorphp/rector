<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\CloningVisitor;
use Rector\NodeVisitor\Traverse\NodeConnectorNodeVisitor;
use Rector\NodeVisitor\Traverse\ParentConnectorNodeVisitor;

final class NodeTraverserFactory
{
    /**
     * @var NodeVisitor[]
     */
    private $nodeVisitors = [];

    /**
     * @var string[]
     */
    private $priorityNodeVisitorClasses = [
        CloningVisitor::class,
        ParentConnectorNodeVisitor::class,
        NodeConnectorNodeVisitor::class
    ];

    public function addNodeVisitor(NodeVisitor $nodeVisitor): void
    {
        $this->nodeVisitors[] = $nodeVisitor;
    }

    public function create(): NodeTraverser
    {
        $nodeTraverser = new NodeTraverser;

        foreach ($this->priorityNodeVisitorClasses as $priorityNodeVisitor) {
            $nodeTraverser->addVisitor(new $priorityNodeVisitor);
        }

        foreach ($this->nodeVisitors as $nodeVisitor) {
            if (in_array(get_class($nodeVisitor), $this->priorityNodeVisitorClasses, true)) {
                continue;
            }

            $nodeTraverser->addVisitor($nodeVisitor);
        }

        return $nodeTraverser;
    }
}
