<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\NodeNameResolver\NodeNameResolver;

final class OverridenExistingNamesResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var string[][]
     */
    private $overridenExistingVariableNamesByClassMethod = [];

    /**
     * @var ArrayFilter
     */
    private $arrayFilter;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        BetterNodeFinder $betterNodeFinder,
        ArrayFilter $arrayFilter
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->arrayFilter = $arrayFilter;
    }

    public function checkNameInClassMethodForNew(string $variableName, FunctionLike $functionLie): bool
    {
        $overridenVariableNames = $this->resolveOveriddenNamesForNew($functionLie);
        return in_array($variableName, $overridenVariableNames, true);
    }

    public function checkNameInClassMethodForParam(string $expectedName, ClassMethod $classMethod): bool
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
    private function resolveOveriddenNamesForNew(FunctionLike $functionLike): array
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
