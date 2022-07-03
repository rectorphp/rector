<?php

declare (strict_types=1);
namespace Rector\Php72\NodeManipulator;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClosureNestedUsesDecorator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
    }
    public function applyNestedUses(Closure $anonymousFunctionNode, Variable $useVariable) : Closure
    {
        $parent = $this->betterNodeFinder->findParentType($useVariable, Closure::class);
        if (!$parent instanceof Closure) {
            return $anonymousFunctionNode;
        }
        $paramNames = $this->nodeNameResolver->getNames($parent->params);
        if ($this->nodeNameResolver->isNames($useVariable, $paramNames)) {
            return $anonymousFunctionNode;
        }
        $anonymousFunctionNode = clone $anonymousFunctionNode;
        while ($parent instanceof Closure) {
            $parentOfParent = $this->betterNodeFinder->findParentType($parent, Closure::class);
            $uses = [];
            while ($parentOfParent instanceof Closure) {
                $uses = $this->collectUsesEqual($parentOfParent, $uses, $useVariable);
                $parentOfParent = $this->betterNodeFinder->findParentType($parentOfParent, Closure::class);
            }
            $uses = \array_merge($parent->uses, $uses);
            $parent->uses = $this->cleanClosureUses($uses);
            $parent = $this->betterNodeFinder->findParentType($parent, Closure::class);
        }
        return $anonymousFunctionNode;
    }
    /**
     * @param ClosureUse[] $uses
     * @return ClosureUse[]
     */
    private function collectUsesEqual(Closure $closure, array $uses, Variable $useVariable) : array
    {
        foreach ($closure->params as $param) {
            if ($this->nodeComparator->areNodesEqual($param->var, $useVariable)) {
                $uses[] = new ClosureUse($param->var);
            }
        }
        return $uses;
    }
    /**
     * @param ClosureUse[] $uses
     * @return ClosureUse[]
     */
    private function cleanClosureUses(array $uses) : array
    {
        $uniqueUses = [];
        foreach ($uses as $use) {
            if (!\is_string($use->var->name)) {
                continue;
            }
            $variableName = $use->var->name;
            if (\array_key_exists($variableName, $uniqueUses)) {
                continue;
            }
            $uniqueUses[$variableName] = $use;
        }
        return \array_values($uniqueUses);
    }
}
