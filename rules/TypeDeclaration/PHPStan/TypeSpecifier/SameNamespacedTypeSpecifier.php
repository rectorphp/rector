<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\PHPStan\TypeSpecifier;

use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RectorPrefix20220606\Rector\TypeDeclaration\Contract\PHPStan\TypeWithClassTypeSpecifierInterface;
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
