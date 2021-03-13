<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeCollector\NodeCollector\ParsedClassConstFetchNodeCollector;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeCollector\NodeCollector\ParsedPropertyFetchNodeCollector;

final class NodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    /**
     * @var ParsedPropertyFetchNodeCollector
     */
    private $parsedPropertyFetchNodeCollector;

    /**
     * @var ParsedClassConstFetchNodeCollector
     */
    private $parsedClassConstFetchNodeCollector;

    public function __construct(
        ParsedClassConstFetchNodeCollector $parsedClassConstFetchNodeCollector,
        NodeRepository $nodeRepository,
        ParsedNodeCollector $parsedNodeCollector,
        ParsedPropertyFetchNodeCollector $parsedPropertyFetchNodeCollector
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->parsedPropertyFetchNodeCollector = $parsedPropertyFetchNodeCollector;
        $this->parsedClassConstFetchNodeCollector = $parsedClassConstFetchNodeCollector;
    }

    public function enterNode(Node $node)
    {
        if ($this->parsedNodeCollector->isCollectableNode($node)) {
            $this->parsedNodeCollector->collect($node);
        }

        $this->nodeRepository->collect($node);
        $this->parsedPropertyFetchNodeCollector->collect($node);
        $this->parsedClassConstFetchNodeCollector->collect($node);

        return null;
    }
}
