<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\PHPStan\TypeSpecifier;

use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\StaticType;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use RectorPrefix20220606\Rector\Core\Enum\ObjectReference;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use RectorPrefix20220606\Rector\TypeDeclaration\Contract\PHPStan\TypeWithClassTypeSpecifierInterface;
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
        $classReference = $objectType->getClassName();
        if ($classReference === ObjectReference::STATIC) {
            return new StaticType($classReflection);
        }
        if ($classReference === ObjectReference::SELF) {
            return new SelfObjectType($classReference, null, $classReflection);
        }
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }
        return new ParentStaticType($parentClassReflection);
    }
}
