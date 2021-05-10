<?php

declare (strict_types=1);
namespace Rector\Naming\Guard\PropertyConflictingNameGuard;

use PhpParser\Node\Stmt\ClassLike;
use Rector\Naming\ExpectedNameResolver\BoolPropertyExpectedNameResolver;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;
final class BoolPropertyConflictingNameGuard
{
    /**
     * @var \Rector\Naming\ExpectedNameResolver\BoolPropertyExpectedNameResolver
     */
    private $boolPropertyExpectedNameResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Naming\PhpArray\ArrayFilter
     */
    private $arrayFilter;
    public function __construct(\Rector\Naming\ExpectedNameResolver\BoolPropertyExpectedNameResolver $boolPropertyExpectedNameResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Naming\PhpArray\ArrayFilter $arrayFilter)
    {
        $this->boolPropertyExpectedNameResolver = $boolPropertyExpectedNameResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFilter = $arrayFilter;
    }
    public function isConflicting(\Rector\Naming\ValueObject\PropertyRename $propertyRename) : bool
    {
        $conflictingPropertyNames = $this->resolve($propertyRename->getClassLike());
        return \in_array($propertyRename->getExpectedName(), $conflictingPropertyNames, \true);
    }
    /**
     * @return string[]
     */
    public function resolve(\PhpParser\Node\Stmt\ClassLike $classLike) : array
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
