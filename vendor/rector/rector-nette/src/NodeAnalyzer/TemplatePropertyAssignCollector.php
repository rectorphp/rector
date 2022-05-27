<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Nette\ValueObject\AlwaysTemplateParameterAssign;
use Rector\Nette\ValueObject\ParameterAssign;
use Rector\Nette\ValueObject\TemplateParametersAssigns;
use Rector\NodeNestingScope\ScopeNestingComparator;
final class TemplatePropertyAssignCollector
{
    /**
     * @var array<class-string<Node>>
     */
    private const NODE_TYPES = [
        // these situations happens only if condition is met
        \PhpParser\Node\Stmt\If_::class,
        \PhpParser\Node\Stmt\While_::class,
        \PhpParser\Node\Stmt\Do_::class,
        \PhpParser\Node\Stmt\Catch_::class,
        \PhpParser\Node\Stmt\Case_::class,
        \PhpParser\Node\Expr\Match_::class,
        \PhpParser\Node\Stmt\Switch_::class,
        \PhpParser\Node\Stmt\Foreach_::class,
        // FunctionLike must be last, so we know the variable is defined in main stmt
        \PhpParser\Node\FunctionLike::class,
    ];
    /**
     * @var \PhpParser\Node\Stmt\Return_|null
     */
    private $lastReturn;
    /**
     * @var AlwaysTemplateParameterAssign[]
     */
    private $alwaysTemplateParameterAssigns = [];
    /**
     * @var AlwaysTemplateParameterAssign[]
     */
    private $defaultChangeableTemplateParameterAssigns = [];
    /**
     * @var ParameterAssign[]
     */
    private $conditionalTemplateParameterAssigns = [];
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ScopeNestingComparator
     */
    private $scopeNestingComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Nette\NodeAnalyzer\ThisTemplatePropertyFetchAnalyzer
     */
    private $thisTemplatePropertyFetchAnalyzer;
    /**
     * @readonly
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
        $this->defaultChangeableTemplateParameterAssigns = [];
        $this->lastReturn = $this->returnAnalyzer->findLastClassMethodReturn($classMethod);
        $assignsOfPropertyFetches = $this->findAssignToPropertyFetches($classMethod);
        $this->collectVariableFromAssign($assignsOfPropertyFetches);
        return new \Rector\Nette\ValueObject\TemplateParametersAssigns($this->alwaysTemplateParameterAssigns, $this->conditionalTemplateParameterAssigns, $this->defaultChangeableTemplateParameterAssigns);
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
                $parentParentType = $this->betterNodeFinder->findParentType($parentType, $nodeType);
                if ($parentParentType instanceof \PhpParser\Node) {
                    $foundParents[] = $parentParentType;
                }
            }
        }
        return $foundParents;
    }
    /**
     * @param Assign[] $assigns
     */
    private function collectVariableFromAssign(array $assigns) : void
    {
        if ($assigns === []) {
            return;
        }
        $fistAssign = $assigns[0];
        /** @var PropertyFetch $propertyFetch */
        $propertyFetch = $fistAssign->var;
        $foundParents = $this->getFoundParents($propertyFetch);
        $isDefaultValueDefined = $this->isDefaultValueDefined($foundParents);
        foreach ($assigns as $assign) {
            $this->processAssign($assign, $isDefaultValueDefined);
        }
    }
    /**
     * @param Node[] $nodes
     */
    private function isDefaultValueDefined(array $nodes) : bool
    {
        if (!isset($nodes[0])) {
            return \false;
        }
        return $nodes[0] instanceof \PhpParser\Node\Stmt\ClassMethod;
    }
    private function processAssign(\PhpParser\Node\Expr\Assign $assign, bool $isDefaultValueDefined) : void
    {
        $parameterName = $this->thisTemplatePropertyFetchAnalyzer->resolveTemplateParameterNameFromAssign($assign);
        if ($parameterName === null) {
            return;
        }
        $propertyFetch = $assign->var;
        if (!$propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $foundParents = $this->getFoundParents($propertyFetch);
        // nested conditions, unreliable, might not be defined
        if (\count($foundParents) >= 3) {
            return;
        }
        foreach ($foundParents as $foundParent) {
            if ($this->scopeNestingComparator->isInBothIfElseBranch($foundParent, $propertyFetch)) {
                $this->conditionalTemplateParameterAssigns[] = new \Rector\Nette\ValueObject\ParameterAssign($assign, $parameterName);
                return;
            }
            if ($foundParent instanceof \PhpParser\Node\Stmt\If_) {
                if ($isDefaultValueDefined) {
                    $this->defaultChangeableTemplateParameterAssigns[] = new \Rector\Nette\ValueObject\AlwaysTemplateParameterAssign($assign, $parameterName, new \PhpParser\Node\Expr\Variable($parameterName));
                    // remove it from always template variables
                    foreach ($this->alwaysTemplateParameterAssigns as $key => $alwaysTemplateParameterAssigns) {
                        if ($alwaysTemplateParameterAssigns->getParameterName() === $parameterName) {
                            unset($this->alwaysTemplateParameterAssigns[$key]);
                        }
                    }
                }
                return;
            }
            // only defined in else branch, nothing we can do
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
    /**
     * @return Assign[]
     */
    private function findAssignToPropertyFetches(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, \PhpParser\Node\Expr\Assign::class);
        $assignsOfPropertyFetches = [];
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            $assignsOfPropertyFetches[] = $assign;
        }
        // re-index from 0
        return \array_values($assignsOfPropertyFetches);
    }
}
