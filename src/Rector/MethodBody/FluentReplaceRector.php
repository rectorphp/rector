<?php declare(strict_types=1);

namespace Rector\Rector\MethodBody;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\MethodCall\ClearedFluentMethodCollector;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class FluentReplaceRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @var ClearedFluentMethodCollector
     */
    private $clearedFluentMethodCollector;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory,
        ClearedFluentMethodCollector $clearedFluentMethodCollector
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->clearedFluentMethodCollector = $clearedFluentMethodCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function someFunction()
    {
        return $this;
    }

    public function otherFunction()
    {
        return $this;
    }
}

$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function someFunction()
    {
        return $this;
    }

    public function otherFunction()
    {
        return $this;
    }
}

$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
CODE_SAMPLE
            ),
        ]);
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $this->isMethodCallCandidate($methodCallNode)) {
            return $methodCallNode;
        }

        /** @var MethodCall $innerMethodCallNode */
        $innerMethodCallNode = $methodCallNode->var;

        $this->decoupleMethodCall($methodCallNode, $innerMethodCallNode);

        return $innerMethodCallNode;
    }

    private function isMethodCallCandidate(MethodCall $methodCallNode): bool
    {
        // is chain method call
        if (! $methodCallNode->var instanceof MethodCall) {
            return false;
        }

        foreach ($this->clearedFluentMethodCollector->getMethodsByClass() as $type => $methods) {
            if (! $this->methodCallAnalyzer->isTypeAndMethods($methodCallNode->var, $type, $methods)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function decoupleMethodCall(MethodCall $outerMethodCallNode, MethodCall $innerMethodCallNode): void
    {
        $nextMethodCallNode = $this->methodCallNodeFactory->createWithVariableAndMethodName(
            $innerMethodCallNode->var,
            (string) $outerMethodCallNode->name
        );

        $this->addNodeAfterNode($nextMethodCallNode, $innerMethodCallNode);
    }
}
