<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Variable;

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
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\TryCatch;
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

        $parentExpression = $expression->getAttribute(AttributeKey::PARENT_NODE);
        if ($this->isUsedAsArrayKey($parentExpression, $node)) {
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

        $variable = $this->getUsageInNextStmts($expression, $node);
        if (! $variable instanceof Variable) {
            return null;
        }

        /** @var Node $usageStmt */
        $usageStmt = $variable->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($this->isInsideLoopStmts($usageStmt)) {
            return null;
        }

        $this->addNodeBeforeNode($expression, $usageStmt);
        $this->removeNode($expression);

        return $node;
    }

    private function isUsedAsArrayKey(?Node $node, Variable $variable): bool
    {
        if (! $node instanceof Node) {
            return false;
        }

        $arrayDimFetches = $this->betterNodeFinder->findInstanceOf($node, ArrayDimFetch::class);

        foreach ($arrayDimFetches as $arrayDimFetch) {
            /** @var Node|null $dim */
            $dim = $arrayDimFetch->dim;
            if (! $dim instanceof Node) {
                continue;
            }

            $isFoundInKey = (bool) $this->betterNodeFinder->findFirst($dim, function (Node $node) use (
                $variable
            ): bool {
                return $this->areNodesEqual($node, $variable);
            });
            if ($isFoundInKey) {
                return true;
            }
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
                $isReAssign = (bool) $this->betterNodeFinder->findFirst($next, function (Node $node): bool {
                    $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
                    $node = $this->mayBeArrayDimFetch($node);
                    return $parent instanceof Assign && (string) $this->getName($node) === (string) $this->getName(
                        $parent->var
                    );
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

        $countFound = $this->getCountFound($next, $node);
        if ($countFound === 0 || $countFound >= 2) {
            return null;
        }

        $variable = $this->getSameVarName([$next], $node);

        if ($variable instanceof Variable) {
            return $variable;
        }

        return $this->getSameVarNameInNexts($next, $node);
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

    private function mayBeArrayDimFetch(Node $node): Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof ArrayDimFetch) {
            $node = $parent->var;
        }

        return $node;
    }

    private function getCountFound(Node $node, Variable $variable): int
    {
        $countFound = 0;
        while ($node) {
            $isFound = (bool) $this->getSameVarName([$node], $variable);

            if ($isFound) {
                ++$countFound;
            }

            $countFound = $this->countWithElseIf($node, $variable, $countFound);
            $countFound = $this->countWithTryCatch($node, $variable, $countFound);
            $countFound = $this->countWithSwitchCase($node, $variable, $countFound);

            /** @var Node|null $node */
            $node = $node->getAttribute(AttributeKey::NEXT_NODE);
        }

        return $countFound;
    }

    /**
     * @param array<int, Node|null> $multiNodes
     */
    private function getSameVarName(array $multiNodes, Node $node): ?Variable
    {
        foreach ($multiNodes as $multiNode) {
            if ($multiNode === null) {
                continue;
            }

            /** @var Variable|null $found */
            $found = $this->betterNodeFinder->findFirst($multiNode, function (Node $n) use ($node): bool {
                $n = $this->mayBeArrayDimFetch($n);
                return $n instanceof Variable && $this->isName($n, (string) $this->getName($node));
            });

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function getSameVarNameInNexts(Node $node, Variable $variable): ?Variable
    {
        while ($node) {
            $found = $this->getSameVarName([$node], $variable);

            if ($found instanceof Variable) {
                return $found;
            }

            /** @var Node|null $node */
            $node = $node->getAttribute(AttributeKey::NEXT_NODE);
        }

        return null;
    }

    private function countWithElseIf(Node $node, Variable $variable, int $countFound): int
    {
        if (! $node instanceof If_) {
            return $countFound;
        }

        $isFoundElseIf = (bool) $this->getSameVarName($node->elseifs, $variable);
        $isFoundElse = (bool) $this->getSameVarName([$node->else], $variable);

        if ($isFoundElseIf || $isFoundElse) {
            ++$countFound;
        }

        return $countFound;
    }

    private function countWithTryCatch(Node $node, Variable $variable, int $countFound): int
    {
        if (! $node instanceof TryCatch) {
            return $countFound;
        }

        $isFoundInCatch = (bool) $this->getSameVarName($node->catches, $variable);
        $isFoundInFinally = (bool) $this->getSameVarName([$node->finally], $variable);

        if ($isFoundInCatch || $isFoundInFinally) {
            ++$countFound;
        }

        return $countFound;
    }

    private function countWithSwitchCase(Node $node, Variable $variable, int $countFound): int
    {
        if (! $node instanceof Switch_) {
            return $countFound;
        }

        $isFoundInCases = (bool) $this->getSameVarName($node->cases, $variable);

        if ($isFoundInCases) {
            ++$countFound;
        }

        return $countFound;
    }
}
