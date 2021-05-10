<?php

declare(strict_types=1);

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
    public function __construct(
        private ValueResolver $valueResolver,
        private NodeTypeResolver $nodeTypeResolver,
        private NodeNameResolver $nodeNameResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    /**
     * @param Variable|PropertyFetch $objectExpr
     */
    public function match(Expr $objectExpr, String_ $string): ?PhpMethodReflection
    {
        $methodName = $this->valueResolver->getValue($string);
        if (! is_string($methodName)) {
            throw new ShouldNotHappenException();
        }

        $objectType = $this->nodeTypeResolver->resolve($objectExpr);

        if ($objectType instanceof ThisType) {
            $objectType = $objectType->getStaticObjectType();
        }

        $objectType = $this->popFirstObjectType($objectType);

        if ($objectType instanceof ObjectType) {
            if (! $this->reflectionProvider->hasClass($objectType->getClassName())) {
                return null;
            }

            $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
            if (! $classReflection->hasMethod($methodName)) {
                return null;
            }

            $stringScope = $string->getAttribute(AttributeKey::SCOPE);

            $methodReflection = $classReflection->getMethod($methodName, $stringScope);
            if (! $methodReflection instanceof PhpMethodReflection) {
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

    private function popFirstObjectType(Type $type): Type
    {
        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (! $unionedType instanceof ObjectType) {
                    continue;
                }

                return $unionedType;
            }
        }

        return $type;
    }
}
