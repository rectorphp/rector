<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\DeadCode\SideEffect\SideEffectNodeDetector
     */
    private $sideEffectNodeDetector;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\DeadCode\SideEffect\SideEffectNodeDetector $sideEffectNodeDetector, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function getUsageInNextStmts(\PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr\Variable $variable) : ?\PhpParser\Node\Expr\Variable
    {
        /** @var Node|null $next */
        $next = $expression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$next instanceof \PhpParser\Node) {
            return null;
        }
        if ($next instanceof \PhpParser\Node\Stmt\InlineHTML) {
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
        if ($nextVariable instanceof \PhpParser\Node\Expr\Variable) {
            return $nextVariable;
        }
        return $this->getSameVarNameInNexts($next, $variable);
    }
    private function hasCall(\PhpParser\Node $node) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($node, function (\PhpParser\Node $n) : bool {
            return $this->sideEffectNodeDetector->detectCallExpr($n);
        });
    }
    private function getCountFound(\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable) : int
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
            $node = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        }
        return $countFound;
    }
    private function getSameVarNameInNexts(\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable) : ?\PhpParser\Node\Expr\Variable
    {
        while ($node) {
            $found = $this->getSameVarName([$node], $variable);
            if ($found instanceof \PhpParser\Node\Expr\Variable) {
                return $found;
            }
            /** @var Node|null $node */
            $node = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        }
        return null;
    }
    private function countWithElseIf(\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable, int $countFound) : int
    {
        if (!$node instanceof \PhpParser\Node\Stmt\If_) {
            return $countFound;
        }
        $isFoundElseIf = (bool) $this->getSameVarName($node->elseifs, $variable);
        $isFoundElse = (bool) $this->getSameVarName([$node->else], $variable);
        if ($isFoundElseIf || $isFoundElse) {
            ++$countFound;
        }
        return $countFound;
    }
    private function countWithTryCatch(\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable, int $countFound) : int
    {
        if (!$node instanceof \PhpParser\Node\Stmt\TryCatch) {
            return $countFound;
        }
        $isFoundInCatch = (bool) $this->getSameVarName($node->catches, $variable);
        $isFoundInFinally = (bool) $this->getSameVarName([$node->finally], $variable);
        if ($isFoundInCatch || $isFoundInFinally) {
            ++$countFound;
        }
        return $countFound;
    }
    private function countWithSwitchCase(\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable, int $countFound) : int
    {
        if (!$node instanceof \PhpParser\Node\Stmt\Switch_) {
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
    private function getSameVarName(array $multiNodes, \PhpParser\Node\Expr\Variable $variable) : ?\PhpParser\Node\Expr\Variable
    {
        foreach ($multiNodes as $multiNode) {
            if ($multiNode === null) {
                continue;
            }
            /** @var Variable|null $found */
            $found = $this->betterNodeFinder->findFirst($multiNode, function (\PhpParser\Node $currentNode) use($variable) : bool {
                $currentNode = $this->unwrapArrayDimFetch($currentNode);
                if (!$currentNode instanceof \PhpParser\Node\Expr\Variable) {
                    return \false;
                }
                return $this->nodeNameResolver->isName($currentNode, (string) $this->nodeNameResolver->getName($variable));
            });
            if ($found !== null) {
                return $found;
            }
        }
        return null;
    }
    private function unwrapArrayDimFetch(\PhpParser\Node $node) : \PhpParser\Node
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        while ($parent instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $node = $parent->var;
            $parent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        }
        return $node;
    }
}
