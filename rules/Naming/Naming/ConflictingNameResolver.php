<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\NodeNameResolver\NodeNameResolver;

final class ConflictingNameResolver
{
    /**
     * @var string[][]
     */
    private array $conflictingVariableNamesByClassMethod = [];

    public function __construct(
        private ArrayFilter $arrayFilter,
        private BetterNodeFinder $betterNodeFinder,
        private ExpectedNameResolver $expectedNameResolver,
        private NodeNameResolver $nodeNameResolver,
        private MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver
    ) {
    }

    /**
     * @return string[]
     */
    public function resolveConflictingVariableNamesForParam(ClassMethod $classMethod): array
    {
        $expectedNames = [];
        foreach ($classMethod->params as $param) {
            $expectedName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($expectedName === null) {
                continue;
            }

            $expectedNames[] = $expectedName;
        }

        return $this->arrayFilter->filterWithAtLeastTwoOccurences($expectedNames);
    }

    public function checkNameIsInFunctionLike(
        string $variableName,
        ClassMethod | Function_ | Closure $functionLike
    ): bool {
        $conflictingVariableNames = $this->resolveConflictingVariableNamesForNew($functionLike);
        return in_array($variableName, $conflictingVariableNames, true);
    }

    /**
     * @return string[]
     */
    private function resolveConflictingVariableNamesForNew(ClassMethod | Function_ | Closure $functionLike): array
    {
        // cache it!
        $classMethodHash = spl_object_hash($functionLike);

        if (isset($this->conflictingVariableNamesByClassMethod[$classMethodHash])) {
            return $this->conflictingVariableNamesByClassMethod[$classMethodHash];
        }

        $paramNames = $this->collectParamNames($functionLike);
        $newAssignNames = $this->resolveForNewAssigns($functionLike);
        $nonNewAssignNames = $this->resolveForNonNewAssigns($functionLike);

        $protectedNames = array_merge($paramNames, $newAssignNames, $nonNewAssignNames);

        $protectedNames = $this->arrayFilter->filterWithAtLeastTwoOccurences($protectedNames);
        $this->conflictingVariableNamesByClassMethod[$classMethodHash] = $protectedNames;

        return $protectedNames;
    }

    /**
     * @return string[]
     */
    private function collectParamNames(ClassMethod | Function_ | Closure $functionLike): array
    {
        $paramNames = [];

        // params
        foreach ($functionLike->params as $param) {
            $paramNames[] = $this->nodeNameResolver->getName($param);
        }

        return $paramNames;
    }

    /**
     * @return string[]
     */
    private function resolveForNewAssigns(ClassMethod | Function_ | Closure $functionLike): array
    {
        $names = [];

        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->stmts, Assign::class);
        foreach ($assigns as $assign) {
            $name = $this->expectedNameResolver->resolveForAssignNew($assign);
            if ($name === null) {
                continue;
            }

            $names[] = $name;
        }

        return $names;
    }

    /**
     * @return string[]
     */
    private function resolveForNonNewAssigns(ClassMethod | Function_ | Closure $functionLike): array
    {
        $names = [];

        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->stmts, Assign::class);
        foreach ($assigns as $assign) {
            $name = $this->expectedNameResolver->resolveForAssignNonNew($assign);
            if ($name === null) {
                continue;
            }

            $names[] = $name;
        }

        return $names;
    }
}
