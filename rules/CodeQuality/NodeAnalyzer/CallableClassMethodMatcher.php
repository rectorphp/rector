<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class CallableClassMethodMatcher
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->valueResolver = $valueResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param Variable|PropertyFetch $objectExpr
     */
    public function match(\PhpParser\Node\Expr $objectExpr, \PhpParser\Node\Scalar\String_ $string) : ?\PHPStan\Reflection\Php\PhpMethodReflection
    {
        $methodName = $this->valueResolver->getValue($string);
        if (!\is_string($methodName)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $objectType = $this->nodeTypeResolver->resolve($objectExpr);
        if ($objectType instanceof \PHPStan\Type\ThisType) {
            $objectType = $objectType->getStaticObjectType();
        }
        $objectType = $this->popFirstObjectType($objectType);
        if ($objectType instanceof \PHPStan\Type\ObjectType) {
            if (!$this->reflectionProvider->hasClass($objectType->getClassName())) {
                return null;
            }
            $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
            if (!$classReflection->hasMethod($methodName)) {
                return null;
            }
            $stringScope = $string->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            $methodReflection = $classReflection->getMethod($methodName, $stringScope);
            if (!$methodReflection instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
                return null;
            }
            if ($this->nodeNameResolver->isName($objectExpr, 'this')) {
                return $methodReflection;
            }
            // is public method of another service
            if ($methodReflection->isPublic()) {
                return $methodReflection;
            }
        }
        return null;
    }
    private function popFirstObjectType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        if ($type instanceof \PHPStan\Type\UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (!$unionedType instanceof \PHPStan\Type\ObjectType) {
                    continue;
                }
                return $unionedType;
            }
        }
        return $type;
    }
}
