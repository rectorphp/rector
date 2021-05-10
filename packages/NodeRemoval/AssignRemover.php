<?php

declare(strict_types=1);

namespace Rector\NodeRemoval;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\DeadCode\NodeManipulator\LivingCodeManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToReplaceCollector;

final class AssignRemover
{
    public function __construct(
        private NodesToReplaceCollector $nodesToReplaceCollector,
        private RectorChangeCollector $rectorChangeCollector,
        private NodeRemover $nodeRemover,
        private LivingCodeManipulator $livingCodeManipulator
    ) {
    }

    public function removeAssignNode(Assign $assign): void
    {
        $currentStatement = $assign->getAttribute(AttributeKey::CURRENT_STATEMENT);
        $this->livingCodeManipulator->addLivingCodeBeforeNode($assign->var, $currentStatement);

        /** @var Assign $assign */
        $parent = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Expression) {
            $this->nodeRemover->removeNode($assign);
        } else {
            $this->nodesToReplaceCollector->addReplaceNodeWithAnotherNode($assign, $assign->expr);
            $this->rectorChangeCollector->notifyNodeFileInfo($assign->expr);
        }
    }
}
