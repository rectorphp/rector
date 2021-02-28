<?php

declare(strict_types=1);

namespace Rector\Naming\Guard\PropertyConflictingNameGuard;

use PhpParser\Node\Stmt\ClassLike;
use Rector\Naming\ExpectedNameResolver\BoolPropertyExpectedNameResolver;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;

final class BoolPropertyConflictingNameGuard
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ArrayFilter
     */
    private $arrayFilter;

    /**
     * @var BoolPropertyExpectedNameResolver
     */
    private $boolPropertyExpectedNameResolver;

    public function __construct(
        BoolPropertyExpectedNameResolver $boolPropertyExpectedNameResolver,
        NodeNameResolver $nodeNameResolver,
        ArrayFilter $arrayFilter
    ) {
        $this->boolPropertyExpectedNameResolver = $boolPropertyExpectedNameResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFilter = $arrayFilter;
    }

    public function isConflicting(PropertyRename $propertyRename): bool
    {
        $conflictingPropertyNames = $this->resolve($propertyRename->getClassLike());
        return in_array($propertyRename->getExpectedName(), $conflictingPropertyNames, true);
    }

    /**
     * @return string[]
     */
    public function resolve(ClassLike $classLike): array
    {
        $expectedNames = [];
        foreach ($classLike->getProperties() as $property) {
            $expectedName = $this->boolPropertyExpectedNameResolver->resolve($property);
            if ($expectedName === null) {
                // fallback to existing name
                $expectedName = $this->nodeNameResolver->getName($property);
            }

            $expectedNames[] = $expectedName;
        }

        return $this->arrayFilter->filterWithAtLeastTwoOccurences($expectedNames);
    }
}
