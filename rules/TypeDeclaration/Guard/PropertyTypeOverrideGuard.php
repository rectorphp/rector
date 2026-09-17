<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Guard;

use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php74\Guard\MakePropertyTypedGuard;
final class PropertyTypeOverrideGuard
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private MakePropertyTypedGuard $makePropertyTypedGuard;
    public function __construct(NodeNameResolver $nodeNameResolver, MakePropertyTypedGuard $makePropertyTypedGuard)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
    }
    public function isLegal(Property $property, ClassReflection $classReflection): bool
    {
        // protected property on a non-final class may be redeclared untyped in a child class,
        // which would cause a covariance fatal error once only the parent is typed
        $inlinePublic = !$property->isProtected();
        if (!$this->makePropertyTypedGuard->isLegal($property, $classReflection, $inlinePublic)) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $nativeReflectionClass = $parentClassReflection->getNativeReflection();
            if (!$nativeReflectionClass->hasProperty($propertyName)) {
                continue;
            }
            $parentPropertyReflection = $nativeReflectionClass->getProperty($propertyName);
            // empty type override is not allowed
            return $parentPropertyReflection->getType() !== null;
        }
        return \true;
    }
}
