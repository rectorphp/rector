<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeRemoval\NodeRemover;
final class RightAssignTemplateRemover
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Nette\NodeAnalyzer\ThisTemplatePropertyFetchAnalyzer
     */
    private $thisTemplatePropertyFetchAnalyzer;
    /**
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Nette\NodeAnalyzer\ThisTemplatePropertyFetchAnalyzer $thisTemplatePropertyFetchAnalyzer, \Rector\NodeRemoval\NodeRemover $nodeRemover)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->thisTemplatePropertyFetchAnalyzer = $thisTemplatePropertyFetchAnalyzer;
        $this->nodeRemover = $nodeRemover;
    }
    public function removeInClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf($classMethod, \PhpParser\Node\Expr\Assign::class);
        foreach ($assigns as $assign) {
            if (!$this->thisTemplatePropertyFetchAnalyzer->isTemplatePropertyFetch($assign->expr)) {
                return;
            }
            $this->nodeRemover->removeNode($assign);
        }
    }
}
