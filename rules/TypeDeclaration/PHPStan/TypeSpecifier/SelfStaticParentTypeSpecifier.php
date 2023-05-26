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
final class SelfStaticParentTypeSpecifier implements TypeWithClassTypeSpecifierInterface
{
    public function match(ObjectType $objectType, Scope $scope) : bool
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return \in_array($objectType->getClassName(), [ObjectReference::STATIC, ObjectReference::PARENT, ObjectReference::SELF], \true);
    }
    public function resolveObjectReferenceType(ObjectType $objectType, Scope $scope) : TypeWithClassName
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }
        $className = $objectType->getClassName();
        if ($className === ObjectReference::STATIC) {
            return new StaticType($classReflection);
        }
        if ($className === ObjectReference::SELF) {
            return new SelfObjectType($className, null, $classReflection);
        }
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }
        return new ParentStaticType($parentClassReflection);
    }
}
