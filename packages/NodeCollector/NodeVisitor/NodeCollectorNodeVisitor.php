<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeCollector\NodeCollector\ParsedPropertyFetchNodeCollector;

final class NodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private NodeRepository $nodeRepository,
        private ParsedNodeCollector $parsedNodeCollector,
        private ParsedPropertyFetchNodeCollector $parsedPropertyFetchNodeCollector
    ) {
    }

    public function enterNode(Node $node)
    {
        if ($this->parsedNodeCollector->isCollectableNode($node)) {
            $this->parsedNodeCollector->collect($node);
        }

        $this->nodeRepository->collect($node);
        $this->parsedPropertyFetchNodeCollector->collect($node);

        return null;
    }
}
