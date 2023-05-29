<?php

declare (strict_types=1);
namespace Rector\NodeRemoval;

use PhpParser\Node;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToRemoveCollector;
final class NodeRemover
{
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    public function __construct(NodesToRemoveCollector $nodesToRemoveCollector, RectorChangeCollector $rectorChangeCollector)
    {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->rectorChangeCollector = $rectorChangeCollector;
    }
    /**
     * @deprecated Return NodeTraverser::* to remove node directly instead
     */
    public function removeNode(Node $node) : void
    {
        // this make sure to keep just added nodes, e.g. added class constant, that doesn't have analysis of full code in this run
        // if this is missing, there are false positive e.g. for unused private constant
        $isJustAddedNode = !(bool) $node->getAttribute(AttributeKey::ORIGINAL_NODE);
        if ($isJustAddedNode) {
            return;
        }
        $this->nodesToRemoveCollector->addNodeToRemove($node);
        $this->rectorChangeCollector->notifyNodeFileInfo($node);
    }
}
