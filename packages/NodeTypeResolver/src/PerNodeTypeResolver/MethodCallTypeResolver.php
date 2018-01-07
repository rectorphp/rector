<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use Nette\Reflection\Method;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;

/**
 * This resolves return type of method call,
 * not types of its elements.
 */
final class MethodCallTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var MethodReflector
     */
    private $methodReflector;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(TypeContext $typeContext, MethodReflector $methodReflector)
    {
        $this->typeContext = $typeContext;
        $this->methodReflector = $methodReflector;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCallNode
     * @return string[]
     */
    public function resolve(Node $methodCallNode): array
    {
        // 1. get $anotherVar type

        /** @var Variable|mixed $variableNode */
        $variableNode = $methodCallNode->var;

        if (! $variableNode instanceof Variable && ! $variableNode instanceof MethodCall) {
            return [];
        }

        $parentCallerTypes = [];

        // chain method calls: $this->someCall()->anotherCall()
        if ($methodCallNode->var instanceof MethodCall) {
            $parentCallerTypes = $this->nodeTypeResolver->resolve($methodCallNode->var);
        }

        // $this->someCall()
        if ($methodCallNode->var instanceof Variable) {
            $parentCallerTypes = $this->nodeTypeResolver->resolve($methodCallNode->var);
        }

        $methodCallName = $methodCallNode->name->toString();
        if (! $parentCallerTypes || ! $methodCallName) {
            return [];
        }


        return $this->resolveMethodReflectionReturnTypes($methodCallNode, $parentCallerTypes, $methodCallName);
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver= $nodeTypeResolver;
    }

    private function getVariableToAssignTo(MethodCall $methodCallNode): ?string
    {
        $assignNode = $methodCallNode->getAttribute(Attribute::PARENT_NODE);
        if (! $assignNode instanceof Assign) {
            return null;
        }

        if ($assignNode->var instanceof Variable) {
            return (string) $assignNode->var->name;
        }

        return null;
    }

    /**
     * Resolve for:
     * - getMethod(): ReturnType
     *
     * @return string[]
     */
    private function resolveMethodReflectionReturnTypes(
        MethodCall $methodCallNode,
        array $methodCallVariableTypes,
        string $method
    ): array {
        $methodReturnTypes = $this->methodReflector->resolveReturnTypesForTypesAndMethod($methodCallVariableTypes, $method);
        if ($methodReturnTypes) {
            return $methodReturnTypes;
        }

        $variableName = $this->getVariableToAssignTo($methodCallNode);
        if ($variableName === null) {
            return [];
        }

        $this->typeContext->addVariableWithTypes($variableName, $methodReturnTypes);

        return $methodReturnTypes;
    }
}
