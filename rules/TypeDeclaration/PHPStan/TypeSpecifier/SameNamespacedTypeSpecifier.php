<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\PHPStan\TypeSpecifier;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\Contract\PHPStan\TypeWithClassTypeSpecifierInterface;
final class SameNamespacedTypeSpecifier implements TypeWithClassTypeSpecifierInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function match(ObjectType $objectType, Scope $scope) : bool
    {
        $namespaceName = $scope->getNamespace();
        if ($namespaceName === null) {
            return \false;
        }
        $namespacedClassName = $namespaceName . '\\' . \ltrim($objectType->getClassName(), '\\');
        return $this->reflectionProvider->hasClass($namespacedClassName);
    }
    public function resolveObjectReferenceType(ObjectType $objectType, Scope $scope) : TypeWithClassName
    {
        $namespacedClassName = $scope->getNamespace() . '\\' . \ltrim($objectType->getClassName(), '\\');
        return new FullyQualifiedObjectType($namespacedClassName);
    }
}
