<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Reflection\Php\PhpPropertyReflection;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\NodeManipulator\FunctionLikeManipulator;
use RectorPrefix20220606\Rector\Core\PhpParser\AstResolver;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\Core\Reflection\ReflectionResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use RectorPrefix20220606\Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use RectorPrefix20220606\Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
final class SetterNodeReturnTypeInfererTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\FunctionLikeManipulator
     */
    private $functionLikeManipulator;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(AssignToPropertyTypeInferer $assignToPropertyTypeInferer, FunctionLikeManipulator $functionLikeManipulator, TypeFactory $typeFactory, BetterNodeFinder $betterNodeFinder, AstResolver $astResolver, ReflectionResolver $reflectionResolver)
    {
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->functionLikeManipulator = $functionLikeManipulator;
        $this->typeFactory = $typeFactory;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->astResolver = $astResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function inferFunctionLike(FunctionLike $functionLike) : Type
    {
        $classLike = $this->betterNodeFinder->findParentType($functionLike, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return new MixedType();
        }
        $returnedPropertyNames = $this->functionLikeManipulator->getReturnedLocalPropertyNames($functionLike);
        $classReflection = $this->reflectionResolver->resolveClassReflection($classLike);
        if (!$classReflection instanceof ClassReflection) {
            return new MixedType();
        }
        $types = [];
        $scope = $classLike->getAttribute(AttributeKey::SCOPE);
        foreach ($returnedPropertyNames as $returnedPropertyName) {
            if (!$classReflection->hasProperty($returnedPropertyName)) {
                continue;
            }
            $propertyReflection = $classReflection->getProperty($returnedPropertyName, $scope);
            if (!$propertyReflection instanceof PhpPropertyReflection) {
                continue;
            }
            $property = $this->astResolver->resolvePropertyFromPropertyReflection($propertyReflection);
            if (!$property instanceof Property) {
                continue;
            }
            $inferredPropertyType = $this->assignToPropertyTypeInferer->inferPropertyInClassLike($property, $returnedPropertyName, $classLike);
            if (!$inferredPropertyType instanceof Type) {
                continue;
            }
            $types[] = $inferredPropertyType;
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    public function getPriority() : int
    {
        return 600;
    }
}
