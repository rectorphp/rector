<?php

declare(strict_types=1);

namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeRemoval\NodeRemover;

final class RightAssignTemplateRemover
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var ThisTemplatePropertyFetchAnalyzer
     */
    private $thisTemplatePropertyFetchAnalyzer;

    /**
     * @var NodeRemover
     */
    private $nodeRemover;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        ThisTemplatePropertyFetchAnalyzer $thisTemplatePropertyFetchAnalyzer,
        NodeRemover $nodeRemover
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->thisTemplatePropertyFetchAnalyzer = $thisTemplatePropertyFetchAnalyzer;
        $this->nodeRemover = $nodeRemover;
    }

    public function removeInClassMethod(ClassMethod $classMethod): void
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf($classMethod, Assign::class);

        foreach ($assigns as $assign) {
            if (! $this->thisTemplatePropertyFetchAnalyzer->isTemplatePropertyFetch($assign->expr)) {
                return;
            }

            $this->nodeRemover->removeNode($assign);
        }
    }
}
