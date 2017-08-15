<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\CloningVisitor;
use Rector\NodeVisitor\Traverse\NodeConnectorNodeVisitor;

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
        NodeConnectorNodeVisitor::class,
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
            if ($this->isPriorityNodeVisitor($nodeVisitor)) {
                continue;
            }

            $nodeTraverser->addVisitor($nodeVisitor);
        }

        return $nodeTraverser;
    }

    private function isPriorityNodeVisitor(NodeVisitor $nodeVisitor): bool
    {
        $nodeVisitorClass = get_class($nodeVisitor);

        return in_array($nodeVisitorClass, $this->priorityNodeVisitorClasses, true);
    }
}
