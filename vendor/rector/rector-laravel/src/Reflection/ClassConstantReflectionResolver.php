<?php

declare (strict_types=1);
namespace Rector\Laravel\Reflection;

use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ClassConstantReflectionResolver
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolveFromClassConstFetch(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch) : ?\PHPStan\Reflection\ConstantReflection
    {
        $constClassType = $this->nodeTypeResolver->getType($classConstFetch->class);
        if (!$constClassType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $className = $constClassType->getClassName();
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $constantName = $this->nodeNameResolver->getName($classConstFetch->name);
        if ($constantName === null) {
            return null;
        }
        if (!$classReflection->hasConstant($constantName)) {
            return null;
        }
        return $classReflection->getConstant($constantName);
    }
}
