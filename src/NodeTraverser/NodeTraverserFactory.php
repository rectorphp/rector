<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

final class NodeTraverserFactory
{
    public function createWithNodeVisitor(NodeVisitor $nodeVisitor): NodeTraverser
    {
        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor($nodeVisitor);

        return $nodeTraverser;
    }
}
