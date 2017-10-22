<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use Nette\DI\Container;
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

final class AssignTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
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

    public function __construct(TypeContext $typeContext)
    {
        $this->typeContext = $typeContext;
    }

    public function getNodeClass(): string
    {
        return Assign::class;
    }

    /**
     * @param Assign $assignNode
     */
    public function resolve(Node $assignNode): ?string
    {
        return null;

        if (! $assignNode->var instanceof Variable) {
            return null;
        }

        // $var = $anotherVar;
        if ($assignNode->expr instanceof Variable) {
            return $this->processAssignVariableNode($assignNode);
//            return $this->nodeTypeResolver->resolve($assignNode);
            // $var = $anotherVar->method();
        }

        if ($assignNode->expr instanceof MethodCall) {
            return $this->processAssignMethodReturn($assignNode);
        }
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    private function processAssignVariableNode(Assign $assignNode): ?string
    {
        if ($assignNode->var->name instanceof Variable) {
            $name = $assignNode->var->name->name;
        } else {
            $name = $assignNode->var->name;
        }

        $this->typeContext->addAssign($name, $assignNode->expr->name);

        $variableType = $this->typeContext->getTypeForVariable($name);
        if ($variableType) {
            $assignNode->var->setAttribute(Attribute::TYPE, $variableType);

            return $variableType;
        }

        return null;
    }

    private function processAssignMethodReturn(Assign $assignNode): ?string
    {
        $variableType = null;

        // 1. get $anotherVar type

        /** @var Variable|mixed $methodCallVariable */
        $methodCallVariable = $assignNode->expr->var;

        if (! $methodCallVariable instanceof Variable) {
            return null;
        }

        $methodCallVariableName = (string) $methodCallVariable->name;

        $methodCallVariableType = $this->typeContext->getTypeForVariable($methodCallVariableName);

        $methodCallName = $this->resolveMethodCallName($assignNode);

        // 2. get method() return type

        if (! $methodCallVariableType || ! $methodCallName) {
            return null;
        }

        $variableType = $this->getMethodReturnType($methodCallVariableType, $methodCallName);

        if ($variableType) {
            $variableName = $assignNode->var->name;
            $this->typeContext->addVariableWithType($variableName, $variableType);
        }

        return $variableType;
    }

    private function getMethodReturnType(string $methodCallVariableType, string $methodCallName): ?string
    {
        $methodReflection = $this->methodReflector->reflectClassMethod($methodCallVariableType, $methodCallName);

        if ($methodReflection) {
            $returnType = $methodReflection->getReturnType();
            if ($returnType) {
                return (string) $returnType;
            }
        }

        return $this->fallbackStaticType($methodCallVariableType, $methodCallName);
    }

    private function resolveMethodCallName(Assign $assignNode): ?string
    {
        if ($assignNode->expr->name instanceof Variable) {
            return $assignNode->expr->name->name;
        }

        if ($assignNode->expr->name instanceof PropertyFetch) {
            // not implemented yet
            return null;
        }

        return (string) $assignNode->expr->name;
    }

    /**
     * Dummy static method call return type that doesn't depend on class reflection.
     *
     * @todo use stubs instead
     */
    private function fallbackStaticType(string $type, string $methodName): ?string
    {
        if ($type === 'Nette\Config\Configurator' && $methodName === 'createContainer') {
            return Container::class;
        }

        return null;
    }
}
