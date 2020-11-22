<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\MoveVariableDeclarationNearReferenceRectorTest
 */
final class MoveVariableDeclarationNearReferenceRector extends AbstractRector
{
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

            ]);
    }

    /**
     * @return string[]
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

        /** @var Expression */
        $expression = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (! $expression instanceof Expression) {
            return null;
        }

        if ($this->isInsideCondition($expression)) {
            return null;
        }

        if ($this->hasPropertyInExpr($expression, $parent->expr)) {
            return null;
        }

        if ($this->hasReAssign($expression, $parent->var) || $this->hasReAssign($expression, $parent->expr)) {
            return null;
        }

        $usageVar = $this->getUsageInNextStmts($expression, $node);
        if (! $usageVar instanceof Variable) {
            return null;
        }

        /** @var Node $usageStmt */
        $usageStmt = $usageVar->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($this->isInsideLoopStmts($usageStmt)) {
            return null;
        }

        $this->addNodeBeforeNode($expression, $usageStmt);
        $this->removeNode($expression);

        return $node;
    }

    private function hasPropertyInExpr(Expression $expression, Expr $expr): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($expr, function (Node $node): bool {
            return $node instanceof PropertyFetch || $node instanceof StaticPropertyFetch;
        });
    }

    private function hasReAssign(Expression $expression, Expr $expr): bool
    {
        $next = $expression->getAttribute(AttributeKey::NEXT_NODE);
        $exprValues = $this->betterNodeFinder->find($expr, function (Node $node): bool {
            return $node instanceof Variable;
        });

        if ($exprValues === []) {
            return false;
        }

        while ($next) {
            foreach ($exprValues as $value) {
                $isReAssign = (bool) $this->betterNodeFinder->findFirst($next, function (Node $node) use (
                    $value
                ): bool {
                    $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
                    return $parent instanceof Assign && $this->areNodesEqual($node, $value) && $parent->var === $node;
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

    private function getUsageInNextStmts(Expression $expression, Node $node): ?Variable
    {
        if (! $node instanceof Variable) {
            return null;
        }

        /** @var Node|null $next */
        $next = $expression->getAttribute(AttributeKey::NEXT_NODE);
        if (! $next instanceof Node) {
            return null;
        }

        $countFound = 0;
        while ($next) {
            $isFound = (bool) $this->betterNodeFinder->findFirst($next, function (Node $n) use ($node): bool {
                return $n instanceof Variable && $this->areNodesEqual($n, $node);
            });

            if ($isFound) {
                ++$countFound;
            }

            if ($next instanceof If_) {
                // check elseif
                $isFoundElseIf = (bool) $this->betterNodeFinder->findFirst($next->elseifs, function (Node $n) use ($node): bool {
                    return $n instanceof Variable && $this->areNodesEqual($n, $node);
                });
                $isFoundElse = (bool) $this->betterNodeFinder->findFirst($next->else, function (Node $n) use ($node): bool {
                    return $n instanceof Variable && $this->areNodesEqual($n, $node);
                });

                if ($isFoundElseIf || $isFoundElse) {
                    ++$countFound;
                }
            }

            if ($countFound === 2) {
                return null;
            }

            /** @var Node|null $next */
            $next = $next->getAttribute(AttributeKey::NEXT_NODE);
        }

        if ($countFound === 0) {
            return null;
        }

        $next = $expression->getAttribute(AttributeKey::NEXT_NODE);
        return $this->betterNodeFinder->findFirst($next, function (Node $n) use ($node): bool {
            return $n instanceof Variable && $this->areNodesEqual($n, $node);
        });
    }

    private function isInsideLoopStmts(Node $node): bool
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);

        while ($parent) {
            if ($parent instanceof For_ || $parent instanceof While_ || $parent instanceof Foreach_ || $parent instanceof Do_) {
                return true;
            }

            $parent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        }

        return false;
    }

    private function isInsideCondition(Node $node): bool
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);

        while ($parent) {
            if ($parent instanceof If_ || $parent instanceof Else_ || $parent instanceof ElseIf_) {
                return true;
            }

            $parent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        }

        return false;
    }
}
