<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Guard;

use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php74\Guard\MakePropertyTypedGuard;
final class PropertyTypeOverrideGuard
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Php74\Guard\MakePropertyTypedGuard
     */
    private $makePropertyTypedGuard;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver, MakePropertyTypedGuard $makePropertyTypedGuard)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
    }
    public function isLegal(Property $property) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($property);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        if (!$this->makePropertyTypedGuard->isLegal($property)) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $nativeReflectionClass = $parentClassReflection->getNativeReflection();
            if (!$nativeReflectionClass->hasProperty($propertyName)) {
                continue;
            }
            $parentPropertyReflection = $nativeReflectionClass->getProperty($propertyName);
            // empty type override is not allowed
            return (\method_exists($parentPropertyReflection, 'getType') ? $parentPropertyReflection->getType() : null) !== null;
        }
        return \true;
    }
}
