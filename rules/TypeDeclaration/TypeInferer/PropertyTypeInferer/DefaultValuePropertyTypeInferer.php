<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
/**
 * Special case of type inferer - it is always added in the end of the resolved types
 */
final class DefaultValuePropertyTypeInferer
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function inferProperty(\PhpParser\Node\Stmt\Property $property) : ?\PHPStan\Type\Type
    {
        $propertyProperty = $property->props[0];
        if ($propertyProperty->default === null) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->nodeTypeResolver->getType($propertyProperty->default);
    }
    public function getPriority() : int
    {
        return 100;
    }
}
