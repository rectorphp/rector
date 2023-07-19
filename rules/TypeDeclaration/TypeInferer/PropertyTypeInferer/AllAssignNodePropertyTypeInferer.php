<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\ClassLikeAstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
final class AllAssignNodePropertyTypeInferer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\ClassLikeAstResolver
     */
    private $classLikeAstResolver;
    public function __construct(AssignToPropertyTypeInferer $assignToPropertyTypeInferer, NodeNameResolver $nodeNameResolver, ClassLikeAstResolver $classLikeAstResolver)
    {
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classLikeAstResolver = $classLikeAstResolver;
    }
    public function inferProperty(Property $property, ClassReflection $classReflection) : ?Type
    {
        /** @var ClassLike $classLike */
        $classLike = $this->classLikeAstResolver->resolveClassFromClassReflection($classReflection);
        $propertyName = $this->nodeNameResolver->getName($property);
        return $this->assignToPropertyTypeInferer->inferPropertyInClassLike($property, $propertyName, $classLike);
    }
}
