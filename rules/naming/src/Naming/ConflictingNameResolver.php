<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\NodeNameResolver\NodeNameResolver;

final class ConflictingNameResolver
{
    /**
     * @var string[][]
     */
    private $conflictingVariableNamesByClassMethod = [];

    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var ArrayFilter
     */
    private $arrayFilter;

    public function __construct(
        ArrayFilter $arrayFilter,
        BetterNodeFinder $betterNodeFinder,
        ExpectedNameResolver $expectedNameResolver,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->expectedNameResolver = $expectedNameResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->arrayFilter = $arrayFilter;
    }

    /**
     * @return string[]
     */
    public function resolveConflictingPropertyNames(ClassLike $classLike): array
    {
        $expectedNames = [];
        foreach ($classLike->getProperties() as $property) {
            $expectedName = $this->expectedNameResolver->resolveForProperty($property);
            if ($expectedName === null) {
                /** @var string $expectedName */
                $expectedName = $this->nodeNameResolver->getName($property);
            }

            $expectedNames[] = $expectedName;
        }

        return $this->arrayFilter->filterWithAtLeastTwoOccurences($expectedNames);
    }

    /**
     * @return string[]
     */
    public function resolveConflictingVariableNamesForParam(ClassMethod $classMethod): array
    {
        $expectedNames = [];
        foreach ($classMethod->params as $param) {
            $expectedName = $this->expectedNameResolver->resolveForParam($param);
            if ($expectedName === null) {
                continue;
            }

            $expectedNames[] = $expectedName;
        }

        return $this->arrayFilter->filterWithAtLeastTwoOccurences($expectedNames);
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function checkNameIsInFunctionLike(string $variableName, FunctionLike $functionLike): bool
    {
        $conflictingVariableNames = $this->resolveConflictingVariableNamesForNew($functionLike);
        return in_array($variableName, $conflictingVariableNames, true);
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     * @return string[]
     */
    private function resolveConflictingVariableNamesForNew(FunctionLike $functionLike): array
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
     * @param ClassMethod|Function_|Closure $functionLike
     * @return string[]
     */
    private function collectParamNames(FunctionLike $functionLike): array
    {
        $paramNames = [];

        // params
        foreach ($functionLike->params as $param) {
            /** @var string $paramName */
            $paramName = $this->nodeNameResolver->getName($param);
            $paramNames[] = $paramName;
        }

        return $paramNames;
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     * @return string[]
     */
    private function resolveForNewAssigns(FunctionLike $functionLike): array
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
     * @param ClassMethod|Function_|Closure $functionLike
     * @return string[]
     */
    private function resolveForNonNewAssigns(FunctionLike $functionLike): array
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
