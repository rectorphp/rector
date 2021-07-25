<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\NodeNameResolver\NodeNameResolver;

final class OverridenExistingNamesResolver
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $overridenExistingVariableNamesByClassMethod = [];

    public function __construct(
        private ArrayFilter $arrayFilter,
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function hasNameInClassMethodForNew(
        string $variableName,
        ClassMethod | Function_ | Closure $functionLike
    ): bool {
        $overridenVariableNames = $this->resolveOveriddenNamesForNew($functionLike);
        return in_array($variableName, $overridenVariableNames, true);
    }

    public function hasNameInClassMethodForParam(string $expectedName, ClassMethod $classMethod): bool
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, Assign::class);

        $usedVariableNames = [];
        foreach ($assigns as $assign) {
            if (! $assign->var instanceof Variable) {
                continue;
            }

            $variableName = $this->nodeNameResolver->getName($assign->var);
            if ($variableName === null) {
                continue;
            }

            $usedVariableNames[] = $variableName;
        }

        return in_array($expectedName, $usedVariableNames, true);
    }

    /**
     * @return string[]
     */
    private function resolveOveriddenNamesForNew(ClassMethod | Function_ | Closure $functionLike): array
    {
        $classMethodHash = spl_object_hash($functionLike);

        if (isset($this->overridenExistingVariableNamesByClassMethod[$classMethodHash])) {
            return $this->overridenExistingVariableNamesByClassMethod[$classMethodHash];
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

        $currentlyUsedNames = array_values($currentlyUsedNames);
        $currentlyUsedNames = $this->arrayFilter->filterWithAtLeastTwoOccurences($currentlyUsedNames);

        $this->overridenExistingVariableNamesByClassMethod[$classMethodHash] = $currentlyUsedNames;

        return $currentlyUsedNames;
    }
}
