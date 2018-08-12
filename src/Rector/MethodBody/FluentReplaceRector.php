<?php declare(strict_types=1);

namespace Rector\Rector\MethodBody;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Inspiration:
 * - https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 * - http://www.yegor256.com/2018/03/13/fluent-interfaces.html
 * - https://github.com/guzzle/guzzle/commit/668209c895049759377593eed129e0949d9565b7#diff-810cdcfdd8a6b9e1fc0d1e96d7786874
 */
final class FluentReplaceRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $relatedTypesAndMethods = [];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, MethodCallNodeFactory $methodCallNodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
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

        // @todo collector service

        foreach ($this->relatedTypesAndMethods as $type => $methods) {
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
