<?php

declare(strict_types=1);

namespace Rector\Php70\Reflection;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Broker\FunctionNotFoundException;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class CallReflection
{
    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(Broker $broker, NodeTypeResolver $nodeTypeResolver, NameResolver $nameResolver)
    {
        $this->broker = $broker;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nameResolver = $nameResolver;
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     *
     * @return array<int, ParameterReflection>
     */
    public function getParameterReflections(Node $node, Scope $scope): array
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            $classType = $this->nodeTypeResolver->getObjectType(
                $node instanceof MethodCall ? $node->var : $node->class
            );
            $methodName = $this->nameResolver->getName($node->name);

            if ($methodName === null || ! $classType->hasMethod($methodName)->yes()) {
                return [];
            }

            return ParametersAcceptorSelector::selectSingle(
                $classType->getMethod($methodName, $scope)->getVariants()
            )->getParameters();
        }

        if ($node->name instanceof Expr) {
            return $this->getParametersForExpr($node->name, $scope);
        }

        return $this->getParametersForName($node->name, $node->args, $scope);
    }

    /**
     * @return array<int, ParameterReflection>
     */
    private function getParametersForExpr(Expr $expr, Scope $scope): array
    {
        $type = $scope->getType($expr);

        if (! $type instanceof ParametersAcceptor) {
            return [];
        }

        return $type->getParameters();
    }

    /**
     * @param Arg[] $args
     *
     * @return array<int, ParameterReflection>
     */
    private function getParametersForName(Name $name, array $args, Scope $scope): array
    {
        try {
            return ParametersAcceptorSelector::selectFromArgs(
                $scope,
                $args,
                $this->broker->getFunction($name, $scope)->getVariants()
            )->getParameters();
        } catch (FunctionNotFoundException $functionNotFoundException) {
            return [];
        }
    }
}
