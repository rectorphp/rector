<?php

declare(strict_types=1);

namespace Rector\CodeQualityStrict\Rector\Variable;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
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
use Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\MoveVariableDeclarationNearReferenceRectorTest
 */
final class MoveVariableDeclarationNearReferenceRector extends AbstractRector
{
    /**
     * @var ScopeAwareNodeFinder
     */
    private $scopeAwareNodeFinder;

    public function __construct(ScopeAwareNodeFinder $scopeAwareNodeFinder)
    {
        $this->scopeAwareNodeFinder = $scopeAwareNodeFinder;
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

        if ($this->hasPropertyInExpr($expression, $parent->expr)) {
            return null;
        }

        if ($this->shouldSkipReAssign($expression, $parent)) {
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

    private function isUsedAsArraykeyOrInsideIfCondition(Expression $expression, Variable $variable): bool
    {
        $parentExpression = $expression->getAttribute(AttributeKey::PARENT_NODE);
        if ($this->isUsedAsArrayKey($parentExpression, $variable)) {
            return true;
        }

        return $this->isInsideCondition($expression);
    }

    private function hasPropertyInExpr(Expression $expression, Expr $expr): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($expr, function (Node $node): bool {
            return $node instanceof PropertyFetch || $node instanceof StaticPropertyFetch;
        });
    }

    private function shouldSkipReAssign(Expression $expression, Assign $assign): bool
    {
        if ($this->hasReAssign($expression, $assign->var)) {
            return true;
        }

        return $this->hasReAssign($expression, $assign->expr);
    }

    private function getUsageInNextStmts(Expression $expression, Variable $variable): ?Variable
    {
        /** @var Node|null $next */
        $next = $expression->getAttribute(AttributeKey::NEXT_NODE);
        if (! $next instanceof Node) {
            return null;
        }

        if ($this->hasCall($next)) {
            return null;
        }

        $countFound = $this->getCountFound($next, $variable);
        if ($countFound === 0) {
            return null;
        }
        if ($countFound >= 2) {
            return null;
        }

        $nextVariable = $this->getSameVarName([$next], $variable);

        if ($nextVariable instanceof Variable) {
            return $nextVariable;
        }

        return $this->getSameVarNameInNexts($next, $variable);
    }

    private function isInsideLoopStmts(Node $node): bool
    {
        $loopNode = $this->betterNodeFinder->findParentTypes(
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
                    if (! $parent instanceof Assign) {
                        return false;
                    }
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

    private function hasCall(Node $node): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($node, function (Node $n): bool {
            if ($n instanceof StaticCall) {
                return true;
            }

            if ($n instanceof MethodCall) {
                return true;
            }

            if (! $n instanceof FuncCall) {
                return false;
            }

            $funcName = $this->getName($n);
            if ($funcName === null) {
                return false;
            }

            return Strings::startsWith($funcName, 'ob_');
        });
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
                if (! $n instanceof Variable) {
                    return false;
                }
                return $this->isName($n, (string) $this->getName($node));
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

    private function mayBeArrayDimFetch(Node $node): Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof ArrayDimFetch) {
            $node = $parent->var;
        }

        return $node;
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
