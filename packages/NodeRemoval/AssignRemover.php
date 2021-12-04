<?php

declare (strict_types=1);
namespace Rector\NodeRemoval;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\DeadCode\NodeManipulator\LivingCodeManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToReplaceCollector;
final class AssignRemover
{
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToReplaceCollector
     */
    private $nodesToReplaceCollector;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    /**
     * @readonly
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\LivingCodeManipulator
     */
    private $livingCodeManipulator;
    public function __construct(\Rector\PostRector\Collector\NodesToReplaceCollector $nodesToReplaceCollector, \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector, \Rector\NodeRemoval\NodeRemover $nodeRemover, \Rector\DeadCode\NodeManipulator\LivingCodeManipulator $livingCodeManipulator)
    {
        $this->nodesToReplaceCollector = $nodesToReplaceCollector;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->nodeRemover = $nodeRemover;
        $this->livingCodeManipulator = $livingCodeManipulator;
    }
    public function removeAssignNode(\PhpParser\Node\Expr\Assign $assign) : void
    {
        $currentStatement = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        $this->livingCodeManipulator->addLivingCodeBeforeNode($assign->var, $currentStatement);
        /** @var Assign $assign */
        $parent = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Stmt\Expression) {
            $this->nodeRemover->removeNode($assign);
        } else {
            $this->nodesToReplaceCollector->addReplaceNodeWithAnotherNode($assign, $assign->expr);
            $this->rectorChangeCollector->notifyNodeFileInfo($assign->expr);
        }
    }
}
