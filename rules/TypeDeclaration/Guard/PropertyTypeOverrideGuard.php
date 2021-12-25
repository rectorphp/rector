<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Guard;

use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class PropertyTypeOverrideGuard
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isLegal(\PhpParser\Node\Stmt\Property $property) : bool
    {
        $propertyName = $this->nodeNameResolver->getName($property);
        $propertyScope = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$propertyScope instanceof \PHPStan\Analyser\Scope) {
            return \true;
        }
        $classReflection = $propertyScope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \true;
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
