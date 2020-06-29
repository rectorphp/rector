<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\NodeNameResolver\NodeNameResolver;

final class ConflictingNameResolver
{
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
     * @var string[][]
     */
    private $conflictingVariableNamesByClassMethod = [];

    /**
     * @var ArrayFilter
     */
    private $arrayFilter;

    public function __construct(
        ExpectedNameResolver $expectedNameResolver,
        NodeNameResolver $nodeNameResolver,
        BetterNodeFinder $betterNodeFinder,
        ArrayFilter $arrayFilter
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

    public function checkNameIsInClassMethod(string $variableName, ClassMethod $classMethod): bool
    {
        $conflictingVariableNames = $this->resolveConflictingVariableNamesForNew($classMethod);
        return in_array($variableName, $conflictingVariableNames, true);
    }

    /**
     * @return string[]
     */
    private function resolveConflictingVariableNamesForNew(ClassMethod $classMethod): array
    {
        // cache it!
        $classMethodHash = spl_object_hash($classMethod);

        if (isset($this->conflictingVariableNamesByClassMethod[$classMethodHash])) {
            return $this->conflictingVariableNamesByClassMethod[$classMethodHash];
        }

        $paramNames = $this->collectParamNames($classMethod);
        $newAssignNames = $this->resolveForNewAssigns($classMethod);
        $nonNewAssignNames = $this->resolveForNonNewAssigns($classMethod);

        $protectedNames = array_merge($paramNames, $newAssignNames, $nonNewAssignNames);

        $protectedNames = $this->arrayFilter->filterWithAtLeastTwoOccurences($protectedNames);
        $this->conflictingVariableNamesByClassMethod[$classMethodHash] = $protectedNames;

        return $protectedNames;
    }

    /**
     * @return string[]
     */
    private function collectParamNames(ClassMethod $classMethod): array
    {
        $paramNames = [];

        // params
        foreach ($classMethod->params as $param) {
            /** @var string $paramName */
            $paramName = $this->nodeNameResolver->getName($param);
            $paramNames[] = $paramName;
        }

        return $paramNames;
    }

    /**
     * @return string[]
     */
    private function resolveForNewAssigns(ClassMethod $classMethod): array
    {
        $names = [];

        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, Assign::class);
        foreach ($assigns as $assign) {
            $name = $this->expectedNameResolver->resolveForAssignNew($assign);
            if ($name === null) {
                continue;
            }

            $names[] = $name;
        }

        return $names;
    }

    private function resolveForNonNewAssigns(ClassMethod $classMethod)
    {
        $names = [];

        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, Assign::class);
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
