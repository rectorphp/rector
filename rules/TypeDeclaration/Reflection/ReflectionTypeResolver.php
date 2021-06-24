<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Reflection;

use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ReflectionTypeResolver
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolvePropertyFetchType(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : ?\PHPStan\Type\Type
    {
        $objectType = $this->nodeTypeResolver->resolve($propertyFetch->var);
        if (!$objectType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
        $propertyName = $this->nodeNameResolver->getName($propertyFetch);
        if ($propertyName === null) {
            return null;
        }
        if ($classReflection->hasProperty($propertyName)) {
            $propertyFetchScope = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            $propertyReflection = $classReflection->getProperty($propertyName, $propertyFetchScope);
            if ($propertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
                return $propertyReflection->getNativeType();
            }
        }
        return null;
    }
}
