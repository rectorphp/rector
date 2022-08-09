<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\NodeManipulator\FunctionLikeManipulator;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
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
            /** @var ClassMemberAccessAnswerer $scope */
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
