<?php

declare(strict_types=1);

namespace Rector\Core\PHPStan\Reflection;

use PhpParser\Node;
use PhpParser\Node\Expr;
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

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     * @return MethodReflection|FunctionReflection|null
     */
    public function resolveCall(Node $node)
    {
        if ($node instanceof FuncCall) {
            return $this->resolveFunctionCall($node);
        }

        return $this->resolveMethodCall($node);
    }

    /**
     * @param FunctionReflection|MethodReflection|null $reflection
     * @param FuncCall|MethodCall|StaticCall|New_ $node
     */
    public function resolveParametersAcceptor($reflection, Node $node): ?ParametersAcceptor
    {
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

    /**
     * @return FunctionReflection|MethodReflection|null
     */
    private function resolveFunctionCall(FuncCall $funcCall)
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

    /**
     * @param MethodCall|StaticCall $expr
     */
    private function resolveMethodCall(Expr $expr): ?MethodReflection
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
