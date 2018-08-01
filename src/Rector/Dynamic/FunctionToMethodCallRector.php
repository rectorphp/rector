<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Node\MethodCallNodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class FunctionToMethodCallRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $functionToMethodCall = [];

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @param string[] $functionToMethodCall e.g. ["view" => ["this", "render"]]
     */
    public function __construct(array $functionToMethodCall, MethodCallNodeFactory $methodCallNodeFactory)
    {
        $this->functionToMethodCall = $functionToMethodCall;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined function calls to local method calls.', [
            new CodeSample('view("...", []);', '$this->render("...", []);'),
        ]);
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
        /** @var Identifier $identifier */
        $identifier = $funcCallNode->name;
        $functionName = $identifier->toString();

        [$variableName, $methodName] = $this->functionToMethodCall[$functionName];

        $methodCallNode = $this->methodCallNodeFactory->createWithVariableNameAndMethodName(
            $variableName,
            $methodName
        );

        $methodCallNode->args = $funcCallNode->args;

        return $methodCallNode;
    }
}
