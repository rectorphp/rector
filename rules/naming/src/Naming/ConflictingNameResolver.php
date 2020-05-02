<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;

final class ConflictingNameResolver
{
    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    public function __construct(ExpectedNameResolver $expectedNameResolver)
    {
        $this->expectedNameResolver = $expectedNameResolver;
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
                continue;
            }

            $expectedNames[] = $expectedName;
        }

        return $this->filterConflictingNames($expectedNames);
    }

    /**
     * @return string[]
     */
    public function resolveConflictingVariableNames(ClassMethod $classMethod): array
    {
        $expectedNames = [];
        foreach ($classMethod->params as $param) {
            $expectedName = $this->expectedNameResolver->resolveForParam($param);
            if ($expectedName === null) {
                continue;
            }

            $expectedNames[] = $expectedName;
        }

        return $this->filterConflictingNames($expectedNames);
    }

    /**
     * @param string[] $expectedNames
     * @return string[]
     */
    private function filterConflictingNames(array $expectedNames): array
    {
        $expectedNamesToCount = array_count_values($expectedNames);

        $conflictingExpectedNames = [];
        foreach ($expectedNamesToCount as $expectedName => $count) {
            if ($count < 2) {
                continue;
            }

            $conflictingExpectedNames[] = $expectedName;
        }

        return $conflictingExpectedNames;
    }
}
