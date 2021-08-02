<?php

declare(strict_types=1);

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
final class MoveVariableDeclarationNearReferenceRector extends AbstractRector
{
    public function __construct(
        private ScopeAwareNodeFinder $scopeAwareNodeFinder,
        private ParentFinder $parentFinder,
        private UsageInNextStmtFinder $usageInNextStmtFinder
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Move variable declaration near its reference',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$var = 1;
if ($condition === null) {
    return $var;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
if ($condition === null) {
    $var = 1;
    return $var;
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! ($parent instanceof Assign && $parent->var === $node)) {
            return null;
        }

        if ($parent->expr instanceof ArrayDimFetch) {
            return null;
        }

        $expression = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (! $expression instanceof Expression) {
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
        if (! $usageStmt instanceof Node) {
            return null;
        }

        if ($this->isInsideLoopStmts($usageStmt)) {
            return null;
        }

        $this->addNodeBeforeNode($expression, $usageStmt);
        $this->removeNode($expression);

        return $node;
    }

    private function isUsedAsArraykeyOrInsideIfCondition(Expression $expression, Variable $variable): bool
    {
        $parentExpression = $expression->getAttribute(AttributeKey::PARENT_NODE);
        if ($this->isUsedAsArrayKey($parentExpression, $variable)) {
            return true;
        }

        return $this->isInsideCondition($expression);
    }

    private function hasPropertyInExpr(Expr $expr): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $expr,
            fn (Node $node): bool => $node instanceof PropertyFetch || $node instanceof StaticPropertyFetch
        );
    }

    private function shouldSkipReAssign(Expression $expression, Assign $assign): bool
    {
        if ($this->hasReAssign($expression, $assign->var)) {
            return true;
        }

        return $this->hasReAssign($expression, $assign->expr);
    }

    private function isInsideLoopStmts(Node $node): bool
    {
        $loopNode = $this->parentFinder->findByTypes(
            $node,
            [For_::class, While_::class, Foreach_::class, Do_::class]
        );
        return (bool) $loopNode;
    }

    private function isUsedAsArrayKey(?Node $node, Variable $variable): bool
    {
        if (! $node instanceof Node) {
            return false;
        }

        /** @var ArrayDimFetch[] $arrayDimFetches */
        $arrayDimFetches = $this->betterNodeFinder->findInstanceOf($node, ArrayDimFetch::class);

        foreach ($arrayDimFetches as $arrayDimFetch) {
            /** @var Node|null $dim */
            $dim = $arrayDimFetch->dim;
            if (! $dim instanceof Node) {
                continue;
            }

            $isFoundInKey = (bool) $this->betterNodeFinder->findFirst(
                $dim,
                fn (Node $node): bool => $this->nodeComparator->areNodesEqual($node, $variable)
            );
            if ($isFoundInKey) {
                return true;
            }
        }

        return false;
    }

    private function isInsideCondition(Expression $expression): bool
    {
        return (bool) $this->scopeAwareNodeFinder->findParentType(
            $expression,
            [If_::class, Else_::class, ElseIf_::class]
        );
    }

    private function hasReAssign(Expression $expression, Expr $expr): bool
    {
        $next = $expression->getAttribute(AttributeKey::NEXT_NODE);
        $exprValues = $this->betterNodeFinder->find($expr, fn (Node $node): bool => $node instanceof Variable);

        if ($exprValues === []) {
            return false;
        }

        while ($next) {
            foreach ($exprValues as $exprValue) {
                $isReAssign = (bool) $this->betterNodeFinder->findFirst($next, function (Node $node): bool {
                    $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
                    if (! $parent instanceof Assign) {
                        return false;
                    }
                    $node = $this->mayBeArrayDimFetch($node);
                    return (string) $this->getName($node) === (string) $this->getName($parent->var);
                });

                if (! $isReAssign) {
                    continue;
                }

                return true;
            }

            $next = $next->getAttribute(AttributeKey::NEXT_NODE);
        }

        return false;
    }

    private function mayBeArrayDimFetch(Node $node): Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof ArrayDimFetch) {
            $node = $parent->var;
        }

        return $node;
    }

    private function findUsageStmt(Expression $expression, Variable $variable): Node | null
    {
        $nextVariable = $this->usageInNextStmtFinder->getUsageInNextStmts($expression, $variable);
        if (! $nextVariable instanceof Variable) {
            return null;
        }

        return $nextVariable->getAttribute(AttributeKey::CURRENT_STATEMENT);
    }
}
