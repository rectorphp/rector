<?php declare(strict_types=1);

namespace Rector\Node;

use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Node\Attribute;

final class MethodCallNodeFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(NodeFactory $nodeFactory, BuilderFactory $builderFactory)
    {
        $this->nodeFactory = $nodeFactory;
        $this->builderFactory = $builderFactory;
    }

    /**
     * Creates "$method->call();"
     */
    public function createWithVariableNameAndMethodName(string $variableName, string $methodName): MethodCall
    {
        $variableNode = $this->nodeFactory->createVariable($variableName);

        return $this->builderFactory->methodCall($variableNode, $methodName);
    }

    /**
     * Creates "$method->call();" from existing variable
     */
    public function createWithVariableAndMethodName(Expr $exprNode, string $methodName): MethodCall
    {
        if ($exprNode instanceof PropertyFetch) {
            $exprNode = $this->clonePropertyFetch($exprNode);
        }

        $methodCallNode = $this->builderFactory->methodCall($exprNode, $methodName);
        $exprNode->setAttribute(Attribute::PARENT_NODE, $methodCallNode);

        return $methodCallNode;
    }

    /**
     * @param Arg[]|string[] $arguments
     */
    public function createWithVariableNameMethodNameAndArguments(
        string $variableName,
        string $methodName,
        array $arguments
    ): MethodCall {
        $methodCallNode = $this->createWithVariableNameAndMethodName($variableName, $methodName);
        $methodCallNode->args = $this->nodeFactory->createArgs($arguments);

        return $methodCallNode;
    }

    /**
     * @param mixed[] $arguments
     */
    public function createWithVariableMethodNameAndArguments(
        Variable $variableNode,
        string $method,
        array $arguments
    ): MethodCall {
        $methodCall = $this->createWithVariableAndMethodName($variableNode, $method);
        $methodCall->args = $this->nodeFactory->createArgs($arguments);

        return $methodCall;
    }

    private function clonePropertyFetch(PropertyFetch $propertyFetchNode): PropertyFetch
    {
        return new PropertyFetch($propertyFetchNode->var, $propertyFetchNode->name);
    }
}
