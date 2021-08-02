<?php

declare(strict_types=1);

namespace Rector\CodeQuality\UsageFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class UsageInNextStmtFinder
{
    public function __construct(
        private SideEffectNodeDetector $sideEffectNodeDetector,
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function getUsageInNextStmts(Expression $expression, Variable $variable): ?Variable
    {
        /** @var Node|null $next */
        $next = $expression->getAttribute(AttributeKey::NEXT_NODE);
        if (! $next instanceof Node) {
            return null;
        }

        if ($next instanceof InlineHTML) {
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

    private function hasCall(Node $node): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $node,
            fn (Node $n): bool => $this->sideEffectNodeDetector->detectCallExpr($n)
        );
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

    /**
     * @param array<int, Node|null> $multiNodes
     */
    private function getSameVarName(array $multiNodes, Variable $variable): ?Variable
    {
        foreach ($multiNodes as $multiNode) {
            if ($multiNode === null) {
                continue;
            }

            /** @var Variable|null $found */
            $found = $this->betterNodeFinder->findFirst($multiNode, function (Node $currentNode) use ($variable): bool {
                $currentNode = $this->unwrapArrayDimFetch($currentNode);
                if (! $currentNode instanceof Variable) {
                    return false;
                }

                return $this->nodeNameResolver->isName(
                    $currentNode,
                    (string) $this->nodeNameResolver->getName($variable)
                );
            });

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function unwrapArrayDimFetch(Node $node): Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        while ($parent instanceof ArrayDimFetch) {
            $node = $parent->var;
            $parent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        }

        return $node;
    }
}
