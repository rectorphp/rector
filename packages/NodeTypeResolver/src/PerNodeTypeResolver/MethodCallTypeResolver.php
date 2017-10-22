<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use Nette\Reflection\Method;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;

final class MethodCallTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

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

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function resolve(Node $methodCallNode): ?string
    {
//        $variableType = null;

        // 1. get $anotherVar type

        /** @var Variable|mixed $methodCallVariable */
        $methodCallVariable = $methodCallNode->var;

        if (! $methodCallVariable instanceof Variable) {
            return null;
        }

        $methodCallVariableName = $methodCallVariable->name;

        $methodCallVariableType = $this->typeContext->getTypeForVariable($methodCallVariableName);

        $methodCallName = $this->resolveMethodCallName($methodCallNode);

        // 2. get method() return type
        if (! $methodCallVariableType || ! $methodCallName) {
            return null;
        }

        $variableType = $this->methodReflector->getMethodReturnType($methodCallVariableType, $methodCallName);
        if ($variableType) {
            $variableName = $this->getVariableToAssignTo($methodCallNode);
            if ($variableName === null) {
                return null;
            }

            $this->typeContext->addVariableWithType($variableName, $variableType);
        }

        return $variableType;
    }

    private function resolveMethodCallName(MethodCall $methodCallNode): ?string
    {
        if ($methodCallNode->name instanceof Variable) {
            return $methodCallNode->name->name;
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
            return $assignNode->var->name;
        }

        return null;
    }
}
