<?php

declare(strict_types=1);

namespace Rector\Core\PHPStan\Reflection;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverRegistry;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class CallReflectionResolver
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private NodeTypeResolver $nodeTypeResolver,
        private ReflectionProvider $reflectionProvider,
        private TypeToCallReflectionResolverRegistry $typeToCallReflectionResolverRegistry
    ) {
    }

    public function resolveConstructor(New_ $new): ?MethodReflection
    {
        $scope = $new->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $classType = $this->nodeTypeResolver->resolve($new->class);

        if ($classType instanceof UnionType) {
            return $this->matchConstructorMethodInUnionType($classType, $scope);
        }

        if (! $classType->hasMethod(MethodName::CONSTRUCT)->yes()) {
            return null;
        }

        return $classType->getMethod(MethodName::CONSTRUCT, $scope);
    }

    public function resolveCall(FuncCall | MethodCall | StaticCall $call): MethodReflection | FunctionReflection | null
    {
        if ($call instanceof FuncCall) {
            return $this->resolveFunctionCall($call);
        }

        return $this->resolveMethodCall($call);
    }

    public function resolveParametersAcceptor(
        FunctionReflection | MethodReflection | null $reflection
    ): ?ParametersAcceptor {
        if ($reflection === null) {
            return null;
        }

        return $reflection->getVariants()[0];
    }

    private function matchConstructorMethodInUnionType(UnionType $unionType, Scope $scope): ?MethodReflection
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                continue;
            }
            if (! $unionedType->hasMethod(MethodName::CONSTRUCT)->yes()) {
                continue;
            }

            return $unionedType->getMethod(MethodName::CONSTRUCT, $scope);
        }

        return null;
    }

    private function resolveFunctionCall(FuncCall $funcCall): FunctionReflection | MethodReflection | null
    {
        /** @var Scope|null $scope */
        $scope = $funcCall->getAttribute(AttributeKey::SCOPE);

        if ($funcCall->name instanceof Name) {
            if ($this->reflectionProvider->hasFunction($funcCall->name, $scope)) {
                return $this->reflectionProvider->getFunction($funcCall->name, $scope);
            }

            return null;
        }

        if (! $scope instanceof Scope) {
            return null;
        }

        $funcCallNameType = $scope->getType($funcCall->name);
        return $this->typeToCallReflectionResolverRegistry->resolve($funcCallNameType, $scope);
    }

    private function resolveMethodCall(MethodCall | StaticCall $expr): ?MethodReflection
    {
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $methodName = $this->nodeNameResolver->getName($expr->name);
        if ($methodName === null) {
            return null;
        }

        $classType = $this->nodeTypeResolver->resolve($expr instanceof MethodCall ? $expr->var : $expr->class);

        if ($classType instanceof ThisType) {
            $classType = $classType->getStaticObjectType();
        }

        if ($classType instanceof ObjectType) {
            if (! $this->reflectionProvider->hasClass($classType->getClassName())) {
                return null;
            }

            $classReflection = $this->reflectionProvider->getClass($classType->getClassName());
            if ($classReflection->hasMethod($methodName)) {
                return $classReflection->getMethod($methodName, $scope);
            }
        }

        return null;
    }
}
