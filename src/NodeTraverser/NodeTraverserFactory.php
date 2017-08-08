<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\CloningVisitor;

final class NodeTraverserFactory
{
    /**
     * @var NodeVisitor[]
     */
    private $nodeVisitors = [];

    public function addNodeVisitor(NodeVisitor $nodeVisitor): void
    {
        $this->nodeVisitors[] = $nodeVisitor;
    }

    public function create(): NodeTraverser
    {
        $nodeTraverser = new NodeTraverser;

        // this one has priority
        $nodeTraverser->addVisitor(new CloningVisitor);

        foreach ($this->nodeVisitors as $nodeVisitor) {
            $nodeTraverser->addVisitor($nodeVisitor);
        }

        return $nodeTraverser;
    }
}
