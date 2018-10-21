<?php declare(strict_types=1);

namespace Rector\Rector\MethodBody;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\MethodCallNodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
     * @param string[] $classesToDefluent
     */
    public function __construct(array $classesToDefluent, MethodCallNodeFactory $methodCallNodeFactory)
    {
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->classesToDefluent = $classesToDefluent;
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
                    '$classesToDefluent' => ['SomeExampleClass'],
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
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // is chain method call
        if (! $node->var instanceof MethodCall) {
            return null;
        }

        // is matching type
        if (! $this->isTypes($node->var, $this->classesToDefluent)) {
            return null;
        }

        /** @var MethodCall $innerMethodCallNode */
        $innerMethodCallNode = $node->var;

        $this->decoupleMethodCall($node, $innerMethodCallNode);

        return $innerMethodCallNode;
    }

    private function decoupleMethodCall(MethodCall $outerMethodCallNode, MethodCall $innerMethodCallNode): void
    {
        $nextMethodCallNode = $this->methodCallNodeFactory->createWithVariableAndMethodName(
            $innerMethodCallNode->var,
            $this->getName($outerMethodCallNode)
        );

        $this->addNodeAfterNode($nextMethodCallNode, $innerMethodCallNode);
    }
}
