<?php

declare (strict_types=1);
namespace Rector\NodeRemoval;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
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
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\PostRector\Collector\NodesToReplaceCollector $nodesToReplaceCollector, \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector, \Rector\NodeRemoval\NodeRemover $nodeRemover, \Rector\DeadCode\NodeManipulator\LivingCodeManipulator $livingCodeManipulator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodesToReplaceCollector = $nodesToReplaceCollector;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->nodeRemover = $nodeRemover;
        $this->livingCodeManipulator = $livingCodeManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function removeAssignNode(\PhpParser\Node\Expr\Assign $assign) : void
    {
        $currentStatement = $this->betterNodeFinder->resolveCurrentStatement($assign);
        if (!$currentStatement instanceof \PhpParser\Node\Stmt) {
            return;
        }
        $this->livingCodeManipulator->addLivingCodeBeforeNode($assign->var, $currentStatement);
        /** @var Assign $assign */
        $parent = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Stmt\Expression) {
            $this->nodeRemover->removeNode($assign);
            return;
        }
        $this->nodesToReplaceCollector->addReplaceNodeWithAnotherNode($assign, $assign->expr);
        $this->rectorChangeCollector->notifyNodeFileInfo($assign->expr);
        if ($parent instanceof \PhpParser\Node\Expr\Assign) {
            $this->removeAssignNode($parent);
        }
    }
}
