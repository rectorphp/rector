<?php

declare(strict_types=1);

namespace Rector\Core\PHPStan\Reflection;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\FunctionNotFoundException;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverRegistry;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class CallReflectionResolver
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var TypeToCallReflectionResolverRegistry
     */
    private $typeToCallReflectionResolverRegistry;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        ReflectionProvider $reflectionProvider,
        TypeToCallReflectionResolverRegistry $typeToCallReflectionResolverRegistry
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeToCallReflectionResolverRegistry = $typeToCallReflectionResolverRegistry;
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

        $variants = $reflection->getVariants();
        $nbVariants = count($variants);

        if ($nbVariants === 0) {
            return null;
        }

        if ($nbVariants === 1) {
            return ParametersAcceptorSelector::selectSingle($variants);
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        return ParametersAcceptorSelector::selectFromArgs($scope, $node->args, $variants);
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
        $scope = $funcCall->getAttribute(AttributeKey::SCOPE);

        if ($funcCall->name instanceof Name) {
            try {
                return $this->reflectionProvider->getFunction($funcCall->name, $scope);
            } catch (FunctionNotFoundException $functionNotFoundException) {
                return null;
            }
        }

        if (! $scope instanceof Scope) {
            return null;
        }

        return $this->typeToCallReflectionResolverRegistry->resolve($scope->getType($funcCall->name), $scope);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function resolveMethodCall(Node $node): ?MethodReflection
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $classType = $this->nodeTypeResolver->resolve($node instanceof MethodCall ? $node->var : $node->class);
        $methodName = $this->nodeNameResolver->getName($node->name);
        if ($methodName === null) {
            return null;
        }
        if (! $classType->hasMethod($methodName)->yes()) {
            return null;
        }

        return $classType->getMethod($methodName, $scope);
    }
}
