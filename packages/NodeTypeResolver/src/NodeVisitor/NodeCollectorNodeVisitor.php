<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeContainer\ParsedNodesByType;

final class NodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(ParsedNodesByType $parsedNodesByType)
    {
        $this->parsedNodesByType = $parsedNodesByType;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if (! $this->parsedNodesByType->isCollectableNode($node)) {
            return;
        }

        $this->parsedNodesByType->collect($node);
    }
}
