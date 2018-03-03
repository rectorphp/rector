<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Node\MethodCallNodeFactory;
use Rector\Rector\AbstractRector;

final class FunctionToMethodCallRector extends AbstractRector
{
    /**
     * "view('...')" => "$this->render('...')"
     *
     * @var string[]
     */
    private $functionToMethodCall = [];

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @param string[] $functionToMethodCall
     */
    public function __construct(array $functionToMethodCall, MethodCallNodeFactory $methodCallNodeFactory)
    {
        $this->functionToMethodCall = $functionToMethodCall;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $node->name instanceof Name) {
            return false;
        }

        $functionName = $node->name->toString();

        return isset($this->functionToMethodCall[$functionName]);
    }

    /**
     * @param FuncCall $funcCallNode
     */
    public function refactor(Node $funcCallNode): ?Node
    {
        $functionName = $funcCallNode->name->toString();

        [$variableName, $methodName] = $this->functionToMethodCall[$functionName];

        $methodCallNode = $this->methodCallNodeFactory->createWithVariableNameAndMethodName(
            $variableName,
            $methodName
        );

        $methodCallNode->args = $funcCallNode->args;

        return $methodCallNode;
    }
}
