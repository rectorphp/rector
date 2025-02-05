<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
final class PropertyAnalyzer
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function hasForbiddenType(Property $property) : bool
    {
        $propertyType = $this->nodeTypeResolver->getType($property);
        if ($propertyType->isNull()->yes()) {
            return \true;
        }
        if ($this->isForbiddenType($propertyType)) {
            return \true;
        }
        if (!$propertyType instanceof UnionType) {
            return \false;
        }
        $types = $propertyType->getTypes();
        foreach ($types as $type) {
            if ($this->isForbiddenType($type)) {
                return \true;
            }
        }
        return \false;
    }
    public function isForbiddenType(Type $type) : bool
    {
        if ($type instanceof NonExistingObjectType) {
            return \true;
        }
        return $this->isCallableType($type);
    }
    private function isCallableType(Type $type) : bool
    {
        if (ClassNameFromObjectTypeResolver::resolve($type) === 'Closure') {
            return \false;
        }
        return $type->isCallable()->yes();
    }
}
