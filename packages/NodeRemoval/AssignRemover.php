<?php

declare(strict_types=1);

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
    public function __construct(
        private readonly NodesToReplaceCollector $nodesToReplaceCollector,
        private readonly RectorChangeCollector $rectorChangeCollector,
        private readonly NodeRemover $nodeRemover,
        private readonly LivingCodeManipulator $livingCodeManipulator,
        private readonly BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function removeAssignNode(Assign $assign): void
    {
        $currentStatement = $this->betterNodeFinder->resolveCurrentStatement($assign);

        if (! $currentStatement instanceof Stmt) {
            return;
        }

        $this->livingCodeManipulator->addLivingCodeBeforeNode($assign->var, $currentStatement);

        /** @var Assign $assign */
        $parent = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Expression) {
            $this->nodeRemover->removeNode($assign);
            return;
        }

        $this->nodesToReplaceCollector->addReplaceNodeWithAnotherNode($assign, $assign->expr);
        $this->rectorChangeCollector->notifyNodeFileInfo($assign->expr);

        if ($parent instanceof Assign) {
            $this->removeAssignNode($parent);
        }
    }
}
