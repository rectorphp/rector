<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeCollector\NodeCollector\NodeRepository;

final class NodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private NodeRepository $nodeRepository,
    ) {
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Class_) {
            $this->nodeRepository->collectClass($node);
        }

        return null;
    }
}
