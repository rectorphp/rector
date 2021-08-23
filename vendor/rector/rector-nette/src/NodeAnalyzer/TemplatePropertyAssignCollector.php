<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Nette\ValueObject\AlwaysTemplateParameterAssign;
use Rector\Nette\ValueObject\ConditionalTemplateParameterAssign;
use Rector\Nette\ValueObject\TemplateParametersAssigns;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Rector\NodeNestingScope\ValueObject\ControlStructure;
final class TemplatePropertyAssignCollector
{
    /**
     * @var array<class-string<\PhpParser\Node>>
     */
    private const NODE_TYPES = \Rector\NodeNestingScope\ValueObject\ControlStructure::CONDITIONAL_NODE_SCOPE_TYPES + [\PhpParser\Node\FunctionLike::class];
    /**
     * @var \PhpParser\Node\Stmt\Return_|null
     */
    private $lastReturn;
    /**
     * @var AlwaysTemplateParameterAssign[]
     */
    private $alwaysTemplateParameterAssigns = [];
    /**
     * @var ConditionalTemplateParameterAssign[]
     */
    private $conditionalTemplateParameterAssigns = [];
    /**
     * @var \Rector\NodeNestingScope\ScopeNestingComparator
     */
    private $scopeNestingComparator;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Nette\NodeAnalyzer\ThisTemplatePropertyFetchAnalyzer
     */
    private $thisTemplatePropertyFetchAnalyzer;
    /**
     * @var \Rector\Nette\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    public function __construct(\Rector\NodeNestingScope\ScopeNestingComparator $scopeNestingComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Nette\NodeAnalyzer\ThisTemplatePropertyFetchAnalyzer $thisTemplatePropertyFetchAnalyzer, \Rector\Nette\NodeAnalyzer\ReturnAnalyzer $returnAnalyzer)
    {
        $this->scopeNestingComparator = $scopeNestingComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->thisTemplatePropertyFetchAnalyzer = $thisTemplatePropertyFetchAnalyzer;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    public function collect(\PhpParser\Node\Stmt\ClassMethod $classMethod) : \Rector\Nette\ValueObject\TemplateParametersAssigns
    {
        $this->alwaysTemplateParameterAssigns = [];
        $this->conditionalTemplateParameterAssigns = [];
        $this->lastReturn = $this->returnAnalyzer->findLastClassMethodReturn($classMethod);
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, \PhpParser\Node\Expr\Assign::class);
        foreach ($assigns as $assign) {
            $this->collectVariableFromAssign($assign);
        }
        return new \Rector\Nette\ValueObject\TemplateParametersAssigns($this->alwaysTemplateParameterAssigns, $this->conditionalTemplateParameterAssigns);
    }
    /**
     * @return Node[]
     */
    private function getFoundParents(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : array
    {
        $foundParents = [];
        /** @var class-string<Node> $nodeType */
        foreach (self::NODE_TYPES as $nodeType) {
            $parentType = $this->betterNodeFinder->findParentType($propertyFetch->var, $nodeType);
            if ($parentType instanceof \PhpParser\Node) {
                $foundParents[] = $parentType;
            }
        }
        return $foundParents;
    }
    private function collectVariableFromAssign(\PhpParser\Node\Expr\Assign $assign) : void
    {
        if (!$assign->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return;
        }
        $parameterName = $this->thisTemplatePropertyFetchAnalyzer->resolveTemplateParameterNameFromAssign($assign);
        if ($parameterName === null) {
            return;
        }
        $propertyFetch = $assign->var;
        $foundParents = $this->getFoundParents($propertyFetch);
        foreach ($foundParents as $foundParent) {
            if ($this->scopeNestingComparator->isInBothIfElseBranch($foundParent, $propertyFetch)) {
                $this->conditionalTemplateParameterAssigns[] = new \Rector\Nette\ValueObject\ConditionalTemplateParameterAssign($assign, $parameterName);
                return;
            }
            if ($foundParent instanceof \PhpParser\Node\Stmt\If_) {
                return;
            }
            if ($foundParent instanceof \PhpParser\Node\Stmt\Else_) {
                return;
            }
        }
        // there is a return before this assign, to do not remove it and keep ti
        if (!$this->returnAnalyzer->isBeforeLastReturn($assign, $this->lastReturn)) {
            return;
        }
        $this->alwaysTemplateParameterAssigns[] = new \Rector\Nette\ValueObject\AlwaysTemplateParameterAssign($assign, $parameterName, $assign->expr);
    }
}
