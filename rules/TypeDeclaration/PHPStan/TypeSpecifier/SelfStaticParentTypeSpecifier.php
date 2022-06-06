<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\PHPStan\TypeSpecifier;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\TypeDeclaration\Contract\PHPStan\TypeWithClassTypeSpecifierInterface;
final class SelfStaticParentTypeSpecifier implements \Rector\TypeDeclaration\Contract\PHPStan\TypeWithClassTypeSpecifierInterface
{
    public function match(\PHPStan\Type\ObjectType $objectType, \PHPStan\Analyser\Scope $scope) : bool
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        return \in_array($objectType->getClassName(), [\Rector\Core\Enum\ObjectReference::STATIC, \Rector\Core\Enum\ObjectReference::PARENT, \Rector\Core\Enum\ObjectReference::SELF], \true);
    }
    public function resolveObjectReferenceType(\PHPStan\Type\ObjectType $objectType, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\TypeWithClassName
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $classReference = $objectType->getClassName();
        if ($classReference === \Rector\Core\Enum\ObjectReference::STATIC) {
            return new \PHPStan\Type\StaticType($classReflection);
        }
        if ($classReference === \Rector\Core\Enum\ObjectReference::SELF) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType($classReference, null, $classReflection);
        }
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return new \Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType($parentClassReflection);
    }
}
