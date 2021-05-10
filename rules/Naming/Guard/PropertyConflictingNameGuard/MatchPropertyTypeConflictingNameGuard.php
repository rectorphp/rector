<?php

declare (strict_types=1);
namespace Rector\Naming\Guard\PropertyConflictingNameGuard;

use PhpParser\Node\Stmt\ClassLike;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;
final class MatchPropertyTypeConflictingNameGuard
{
    /**
     * @var \Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver
     */
    private $matchPropertyTypeExpectedNameResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Naming\PhpArray\ArrayFilter
     */
    private $arrayFilter;
    public function __construct(\Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver $matchPropertyTypeExpectedNameResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Naming\PhpArray\ArrayFilter $arrayFilter)
    {
        $this->matchPropertyTypeExpectedNameResolver = $matchPropertyTypeExpectedNameResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFilter = $arrayFilter;
    }
    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting(\Rector\Naming\Contract\RenameValueObjectInterface $renameValueObject) : bool
    {
        $conflictingPropertyNames = $this->resolve($renameValueObject->getClassLike());
        return \in_array($renameValueObject->getExpectedName(), $conflictingPropertyNames, \true);
    }
    /**
     * @return string[]
     */
    public function resolve(\PhpParser\Node\Stmt\ClassLike $classLike) : array
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
