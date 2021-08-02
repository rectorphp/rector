<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use Rector\CodeQuality\UsageFinder\UsageInNextStmtFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder;
use Rector\NodeNestingScope\ParentFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\MoveVariableDeclarationNearReferenceRectorTest
 */
final class MoveVariableDeclarationNearReferenceRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder
     */
    private $scopeAwareNodeFinder;
    /**
     * @var \Rector\NodeNestingScope\ParentFinder
     */
    private $parentFinder;
    /**
     * @var \Rector\CodeQuality\UsageFinder\UsageInNextStmtFinder
     */
    private $usageInNextStmtFinder;
    public function __construct(\Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder $scopeAwareNodeFinder, \Rector\NodeNestingScope\ParentFinder $parentFinder, \Rector\CodeQuality\UsageFinder\UsageInNextStmtFinder $usageInNextStmtFinder)
    {
        $this->scopeAwareNodeFinder = $scopeAwareNodeFinder;
        $this->parentFinder = $parentFinder;
        $this->usageInNextStmtFinder = $usageInNextStmtFinder;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move variable declaration near its reference', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$var = 1;
if ($condition === null) {
    return $var;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if ($condition === null) {
    $var = 1;
    return $var;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Variable::class];
    }
    /**
     * @param Variable $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!($parent instanceof \PhpParser\Node\Expr\Assign && $parent->var === $node)) {
            return null;
        }
        if ($parent->expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        $expression = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$expression instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if ($this->isUsedAsArraykeyOrInsideIfCondition($expression, $node)) {
            return null;
        }
        if ($this->hasPropertyInExpr($parent->expr)) {
            return null;
        }
        if ($this->shouldSkipReAssign($expression, $parent)) {
            return null;
        }
        $usageStmt = $this->findUsageStmt($expression, $node);
        if (!$usageStmt instanceof \PhpParser\Node) {
            return null;
        }
        if ($this->isInsideLoopStmts($usageStmt)) {
            return null;
        }
        $this->addNodeBeforeNode($expression, $usageStmt);
        $this->removeNode($expression);
        return $node;
    }
    private function isUsedAsArraykeyOrInsideIfCondition(\PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        $parentExpression = $expression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($this->isUsedAsArrayKey($parentExpression, $variable)) {
            return \true;
        }
        return $this->isInsideCondition($expression);
    }
    private function hasPropertyInExpr(\PhpParser\Node\Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($expr, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Expr\PropertyFetch || $node instanceof \PhpParser\Node\Expr\StaticPropertyFetch;
        });
    }
    private function shouldSkipReAssign(\PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr\Assign $assign) : bool
    {
        if ($this->hasReAssign($expression, $assign->var)) {
            return \true;
        }
        return $this->hasReAssign($expression, $assign->expr);
    }
    private function isInsideLoopStmts(\PhpParser\Node $node) : bool
    {
        $loopNode = $this->parentFinder->findByTypes($node, [\PhpParser\Node\Stmt\For_::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\Stmt\Do_::class]);
        return (bool) $loopNode;
    }
    private function isUsedAsArrayKey(?\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        if (!$node instanceof \PhpParser\Node) {
            return \false;
        }
        /** @var ArrayDimFetch[] $arrayDimFetches */
        $arrayDimFetches = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Expr\ArrayDimFetch::class);
        foreach ($arrayDimFetches as $arrayDimFetch) {
            /** @var Node|null $dim */
            $dim = $arrayDimFetch->dim;
            if (!$dim instanceof \PhpParser\Node) {
                continue;
            }
            $isFoundInKey = (bool) $this->betterNodeFinder->findFirst($dim, function (\PhpParser\Node $node) use($variable) : bool {
                return $this->nodeComparator->areNodesEqual($node, $variable);
            });
            if ($isFoundInKey) {
                return \true;
            }
        }
        return \false;
    }
    private function isInsideCondition(\PhpParser\Node\Stmt\Expression $expression) : bool
    {
        return (bool) $this->scopeAwareNodeFinder->findParentType($expression, [\PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\Else_::class, \PhpParser\Node\Stmt\ElseIf_::class]);
    }
    private function hasReAssign(\PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr $expr) : bool
    {
        $next = $expression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        $exprValues = $this->betterNodeFinder->find($expr, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Expr\Variable;
        });
        if ($exprValues === []) {
            return \false;
        }
        while ($next) {
            foreach ($exprValues as $exprValue) {
                $isReAssign = (bool) $this->betterNodeFinder->findFirst($next, function (\PhpParser\Node $node) : bool {
                    $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
                    if (!$parent instanceof \PhpParser\Node\Expr\Assign) {
                        return \false;
                    }
                    $node = $this->mayBeArrayDimFetch($node);
                    return (string) $this->getName($node) === (string) $this->getName($parent->var);
                });
                if (!$isReAssign) {
                    continue;
                }
                return \true;
            }
            $next = $next->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        }
        return \false;
    }
    private function mayBeArrayDimFetch(\PhpParser\Node $node) : \PhpParser\Node
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $node = $parent->var;
        }
        return $node;
    }
    /**
     * @return \PhpParser\Node|null
     */
    private function findUsageStmt(\PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr\Variable $variable)
    {
        $nextVariable = $this->usageInNextStmtFinder->getUsageInNextStmts($expression, $variable);
        if (!$nextVariable instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        return $nextVariable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
    }
}
