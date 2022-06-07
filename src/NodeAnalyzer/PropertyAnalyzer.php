<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\CallableType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;
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
        if ($this->isCallableType($propertyType)) {
            return \true;
        }
        if (!$propertyType instanceof UnionType) {
            return \false;
        }
        $types = $propertyType->getTypes();
        foreach ($types as $type) {
            if ($this->isCallableType($type)) {
                return \true;
            }
        }
        return \false;
    }
    private function isCallableType(Type $type) : bool
    {
        if ($type instanceof TypeWithClassName) {
            return $type->getClassName() === 'Closure';
        }
        return $type instanceof CallableType;
    }
}
