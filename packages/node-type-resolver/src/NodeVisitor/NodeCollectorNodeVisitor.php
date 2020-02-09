<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\NodeContainer\NodeCollector\ParsedFunctionLikeNodeCollector;
use Rector\Core\NodeContainer\NodeCollector\ParsedNodeCollector;

final class NodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ParsedFunctionLikeNodeCollector
     */
    private $parsedFunctionLikeNodeCollector;

    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    public function __construct(
        ParsedNodeCollector $parsedNodeCollector,
        ParsedFunctionLikeNodeCollector $parsedFunctionLikeNodeCollector
    ) {
        $this->parsedFunctionLikeNodeCollector = $parsedFunctionLikeNodeCollector;
        $this->parsedNodeCollector = $parsedNodeCollector;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if ($this->parsedNodeCollector->isCollectableNode($node)) {
            $this->parsedNodeCollector->collect($node);
        }

        $this->parsedFunctionLikeNodeCollector->collect($node);
    }
}
