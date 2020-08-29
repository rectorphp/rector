<?php

declare(strict_types=1);

namespace Rector\Generic\NodeTypeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class CallTypeAnalyzer
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        ReflectionProvider $reflectionProvider,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @param MethodCall|StaticCall $node
     * @return Type[]
     */
    public function resolveMethodParameterTypes(Node $node): array
    {
        $callerType = $this->resolveCallerType($node);
        if (! $callerType instanceof TypeWithClassName) {
            return [];
        }

        $callerClassName = $callerType->getClassName();

        return $this->getMethodParameterTypes($callerClassName, $node);
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    private function resolveCallerType(Node $node): Type
    {
        if ($node instanceof MethodCall) {
            return $this->nodeTypeResolver->getStaticType($node->var);
        }

        return $this->nodeTypeResolver->resolve($node->class);
    }

    /**
     * @param MethodCall|StaticCall $node
     * @return Type[]
     */
    private function getMethodParameterTypes(string $className, Node $node): array
    {
        $classReflection = $this->reflectionProvider->getClass($className);

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($node->name);

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return [];
        }

        $methodReflection = $classReflection->getMethod($methodName, $scope);
        $functionVariant = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());

        $parameterTypes = [];

        /** @var ParameterReflection $parameter */
        foreach ($functionVariant->getParameters() as $parameter) {
            $parameterTypes[] = $parameter->getType();
        }

        return $parameterTypes;
    }
}
