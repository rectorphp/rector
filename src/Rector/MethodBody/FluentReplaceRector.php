<?php declare(strict_types=1);

namespace Rector\Rector\MethodBody;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\CallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use SomeClass;

final class FluentReplaceRector extends AbstractRector
{
    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @var string[]
     */
    private $classesToDefluent = [];

    /**
     * @var CallAnalyzer
     */
    private $callAnalyzer;

    /**
     * @param string[] $classesToDefluent
     */
    public function __construct(
        array $classesToDefluent,
        MethodCallNodeFactory $methodCallNodeFactory,
        CallAnalyzer $callAnalyzer
    ) {
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->classesToDefluent = $classesToDefluent;
        $this->callAnalyzer = $callAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
CODE_SAMPLE
                ,
                [
                    '$classesToDefluent' => [SomeClass::class],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        // is chain method call
        if (! $methodCallNode->var instanceof MethodCall) {
            return $methodCallNode;
        }

        // is matching type
        if (! $this->isTypes($methodCallNode->var->var, $this->classesToDefluent)) {
            return $methodCallNode;
        }

        /** @var MethodCall $innerMethodCallNode */
        $innerMethodCallNode = $methodCallNode->var;

        $this->decoupleMethodCall($methodCallNode, $innerMethodCallNode);

        return $innerMethodCallNode;
    }

    private function decoupleMethodCall(MethodCall $outerMethodCallNode, MethodCall $innerMethodCallNode): void
    {
        $nextMethodCallNode = $this->methodCallNodeFactory->createWithVariableAndMethodName(
            $innerMethodCallNode->var,
            $this->callAnalyzer->resolveName($outerMethodCallNode)
        );

        $this->addNodeAfterNode($nextMethodCallNode, $innerMethodCallNode);
    }
}
