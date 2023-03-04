<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\CallableType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
final class PropertyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function hasForbiddenType(Property $property) : bool
    {
        $propertyType = $this->nodeTypeResolver->getType($property);
        if ($propertyType instanceof NullType) {
            return \true;
        }
        if ($this->isForbiddenType($property, $propertyType)) {
            return \true;
        }
        if (!$propertyType instanceof UnionType) {
            return \false;
        }
        $types = $propertyType->getTypes();
        foreach ($types as $type) {
            if ($this->isForbiddenType($property, $type)) {
                return \true;
            }
        }
        return \false;
    }
    private function isForbiddenType(Property $property, Type $type) : bool
    {
        if ($type instanceof NonExistingObjectType) {
            return \true;
        }
        return $this->isCallableType($property, $type);
    }
    private function isCallableType(Property $property, Type $type) : bool
    {
        if ($type instanceof TypeWithClassName && $type->getClassName() === 'Closure') {
            return !$property->type instanceof Node;
        }
        return $type instanceof CallableType;
    }
}
