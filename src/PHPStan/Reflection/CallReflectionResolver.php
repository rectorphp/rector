<?php

declare(strict_types=1);

namespace Rector\PHPStan\Reflection;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\FunctionNotFoundException;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ClosureType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PHPStan\Reflection\Php\ClosureInvokeMethodReflection;

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
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(
        ReflectionProvider $reflectionProvider,
        NodeTypeResolver $nodeTypeResolver,
        NameResolver $nameResolver
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nameResolver = $nameResolver;
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     * @return FunctionReflection|MethodReflection|null
     */
    public function resolveCall(Node $node)
    {
        if ($node instanceof FuncCall) {
            return $this->resolveFunctionCall($node);
        }

        return $this->resolveMethodCall($node);
    }

    /**
     * @return FunctionReflection|MethodReflection|null
     */
    public function resolveFunctionCall(FuncCall $funcCall)
    {
        /** @var Scope|null $scope */
        $scope = $funcCall->getAttribute(AttributeKey::SCOPE);

        if ($funcCall->name instanceof Name) {
            try {
                return $this->reflectionProvider->getFunction($funcCall->name, $scope);
            } catch (FunctionNotFoundException $functionNotFoundException) {
                return null;
            }
        }

        if ($scope === null) {
            return null;
        }

        $type = $scope->getType($funcCall->name);
        if (! $type instanceof ClosureType) {
            return null;
        }

        return new ClosureInvokeMethodReflection($type->getMethod('__invoke', $scope), $type);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function resolveMethodCall(Node $node): ?MethodReflection
    {
        /** @var Scope|null $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return null;
        }

        $classType = $this->nodeTypeResolver->getObjectType(
            $node instanceof MethodCall ? $node->var : $node->class
        );
        $methodName = $this->nameResolver->getName($node->name);

        if ($methodName === null || ! $classType->hasMethod($methodName)->yes()) {
            return null;
        }

        return $classType->getMethod($methodName, $scope);
    }

    /**
     * @param FunctionReflection|MethodReflection|null $reflection
     * @param FuncCall|MethodCall|StaticCall $node
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
        } elseif ($nbVariants === 1) {
            $parametersAcceptor = ParametersAcceptorSelector::selectSingle($variants);
        } else {
            /** @var Scope|null $scope */
            $scope = $node->getAttribute(AttributeKey::SCOPE);
            if ($scope === null) {
                return null;
            }

            $parametersAcceptor = ParametersAcceptorSelector::selectFromArgs($scope, $node->args, $variants);
        }

        return $parametersAcceptor;
    }
}
