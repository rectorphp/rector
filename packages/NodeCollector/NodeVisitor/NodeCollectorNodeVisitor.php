<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;

final class NodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private ParsedNodeCollector $parsedNodeCollector,
    ) {
    }

    public function enterNode(Node $node)
    {
        if ($this->parsedNodeCollector->isCollectableNode($node)) {
            $this->parsedNodeCollector->collect($node);
        }

        return null;
    }
}
