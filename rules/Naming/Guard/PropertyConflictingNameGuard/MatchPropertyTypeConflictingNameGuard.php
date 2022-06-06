<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Guard\PropertyConflictingNameGuard;

use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\Rector\Naming\Contract\RenameValueObjectInterface;
use RectorPrefix20220606\Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;
use RectorPrefix20220606\Rector\Naming\PhpArray\ArrayFilter;
use RectorPrefix20220606\Rector\Naming\ValueObject\PropertyRename;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class MatchPropertyTypeConflictingNameGuard
{
    /**
     * @readonly
     * @var \Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver
     */
    private $matchPropertyTypeExpectedNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\PhpArray\ArrayFilter
     */
    private $arrayFilter;
    public function __construct(MatchPropertyTypeExpectedNameResolver $matchPropertyTypeExpectedNameResolver, NodeNameResolver $nodeNameResolver, ArrayFilter $arrayFilter)
    {
        $this->matchPropertyTypeExpectedNameResolver = $matchPropertyTypeExpectedNameResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFilter = $arrayFilter;
    }
    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting(RenameValueObjectInterface $renameValueObject) : bool
    {
        $conflictingPropertyNames = $this->resolve($renameValueObject->getClassLike());
        return \in_array($renameValueObject->getExpectedName(), $conflictingPropertyNames, \true);
    }
    /**
     * @return string[]
     */
    public function resolve(ClassLike $classLike) : array
    {
        $expectedNames = [];
        foreach ($classLike->getProperties() as $property) {
            $expectedName = $this->matchPropertyTypeExpectedNameResolver->resolve($property);
            if ($expectedName === null) {
                // fallback to existing name
                $expectedName = $this->nodeNameResolver->getName($property);
            }
            $expectedNames[] = $expectedName;
        }
        return $this->arrayFilter->filterWithAtLeastTwoOccurences($expectedNames);
    }
}
