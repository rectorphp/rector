<?php declare(strict_types=1);

namespace Rector\Node;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

final class MethodCallNodeFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * Creates "$method->call();"
     */
    public function createMethodCall(string $variableName, string $methodName): MethodCall
    {
        $variableNode = $this->nodeFactory->createVariable($variableName);

        return new MethodCall($variableNode, $methodName);
    }

    /**
     * Creates "$method->call();" from existing variable
     */
    public function createMethodCallWithVariable(Expr $exprNode, string $methodName): MethodCall
    {
        $methodCallNode = new MethodCall($exprNode, $methodName);
        $exprNode->setAttribute(Attribute::PARENT_NODE, $methodCallNode);

        return $methodCallNode;
    }

    /**
     * @param Arg[] $arguments
     */
    public function createMethodCallWithArguments(
        string $variableName,
        string $methodName,
        array $arguments
    ): MethodCall {
        $methodCallNode = $this->createMethodCall($variableName, $methodName);
        $methodCallNode->args = $arguments;

        return $methodCallNode;
    }

    /**
     * @param mixed[] $arguments
     */
    public function createMethodCallWithVariableAndArguments(
        Variable $variableNode,
        string $method,
        array $arguments
    ): MethodCall {
        $methodCall = $this->createMethodCallWithVariable($variableNode, $method);
        $methodCall->args = $this->nodeFactory->createArgs($arguments);

        return $methodCall;
    }

    /**
     * @param mixed[] $arguments
     */
    public function createStaticMethodCallWithArgs(string $class, string $method, array $arguments): StaticCall
    {
        return new StaticCall(new Name($class), new Identifier($method), $arguments);
    }
}
