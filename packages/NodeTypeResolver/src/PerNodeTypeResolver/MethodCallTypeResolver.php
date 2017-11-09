<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\TypeContext;

/**
 * This resolves return type of method call,
 * not types of its elements.
 */
final class MethodCallTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var MethodReflector
     */
    private $methodReflector;

    public function __construct(TypeContext $typeContext, MethodReflector $methodReflector)
    {
        $this->typeContext = $typeContext;
        $this->methodReflector = $methodReflector;
    }

    public function getNodeClass(): string
    {
        return MethodCall::class;
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

        if (! $variableNode instanceof Variable) {
            return [];
        }

        $variableName = (string) $variableNode->name;

        $methodCallVariableTypes = $this->typeContext->getTypesForVariable($variableName);

        $methodCallName = $this->resolveMethodCallName($methodCallNode);

        if (! $methodCallVariableTypes || ! $methodCallName) {
            return [];
        }

        $methodCallVariableType = array_shift($methodCallVariableTypes);

        return $this->resolveMethodReflectionReturnTypes($methodCallNode, $methodCallVariableType, $methodCallName);
    }

    private function resolveMethodCallName(MethodCall $methodCallNode): ?string
    {
        if ($methodCallNode->name instanceof Variable) {
            return (string) $methodCallNode->name->name;
        }

        if ($methodCallNode->name instanceof PropertyFetch) {
            // not implemented yet
            return null;
        }

        return (string) $methodCallNode->name;
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
        string $class,
        string $method
    ): array {
        $variableTypes = $this->methodReflector->getMethodReturnTypes($class, $method);
        if (! $variableTypes) {
            return [];
        }

        $variableName = $this->getVariableToAssignTo($methodCallNode);
        if ($variableName === null) {
            return [];
        }

        $this->typeContext->addVariableWithTypes($variableName, $variableTypes);

        return $variableTypes;
    }
}
