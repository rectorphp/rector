<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class OverriddenExistingNamesResolver
{
    /**
     * @readonly
     */
    private ArrayFilter $arrayFilter;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @var array<int, array<int, string>>
     */
    private array $overriddenExistingVariableNamesByClassMethod = [];
    public function __construct(ArrayFilter $arrayFilter, BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->arrayFilter = $arrayFilter;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function hasNameInClassMethodForNew(string $variableName, ClassMethod $classMethod): bool
    {
        $overriddenVariableNames = $this->resolveOverriddenNamesForNew($classMethod);
        return in_array($variableName, $overriddenVariableNames, \true);
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function resolveOverriddenNamesForNew($functionLike): array
    {
        $classMethodId = spl_object_id($functionLike);
        if (isset($this->overriddenExistingVariableNamesByClassMethod[$classMethodId])) {
            return $this->overriddenExistingVariableNamesByClassMethod[$classMethodId];
        }
        $currentlyUsedNames = [];
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->stmts, Assign::class);
        foreach ($assigns as $assign) {
            /** @var Variable $assignVariable */
            $assignVariable = $assign->var;
            $currentVariableName = $this->nodeNameResolver->getName($assignVariable);
            if ($currentVariableName === null) {
                continue;
            }
            $currentlyUsedNames[] = $currentVariableName;
        }
        $currentlyUsedNames = $this->arrayFilter->filterWithAtLeastTwoOccurrences($currentlyUsedNames);
        $this->overriddenExistingVariableNamesByClassMethod[$classMethodId] = $currentlyUsedNames;
        return $currentlyUsedNames;
    }
}
