<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class DelegateExceptionArgumentsRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $oldToNewMethod = [
        'setExpectedException' => 'expectExceptionMessage',
        'setExpectedExceptionRegExp' => 'expectExceptionMessageRegExp',
    ];

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
        return new RectorDefinition(
            'Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.',
            [
                new CodeSample(
                    '$this->setExpectedException(Exception::class, "Message", "CODE");',
                    <<<'CODE_SAMPLE'
$this->setExpectedException(Exception::class);
$this->expectExceptionMessage("Message");
$this->expectExceptionCode("CODE");
CODE_SAMPLE
                ),
            ]
        );
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
        if (! $methodCallNode instanceof MethodCall) {
            return null;
        }
        if (! $this->isInTestClass($methodCallNode)) {
            return null;
        }
        if (! $this->methodCallAnalyzer->isMethods($methodCallNode, array_keys($this->oldToNewMethod))) {
            return null;
        }
        /** @var MethodCall $methodCallNode */
        $methodCallNode = $methodCallNode;
        if (isset($methodCallNode->args[1]) === false) {
            return null;
        }
        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNode->name;
        $oldMethodName = $identifierNode->name;

        $this->addNewMethodCall($methodCallNode, $this->oldToNewMethod[$oldMethodName], $methodCallNode->args[1]);
        unset($methodCallNode->args[1]);

        // add exception code method call
        if (isset($methodCallNode->args[2])) {
            $this->addNewMethodCall($methodCallNode, 'expectExceptionCode', $methodCallNode->args[2]);
            unset($methodCallNode->args[2]);
        }

        return $methodCallNode;
    }

    private function addNewMethodCall(MethodCall $methodCallNode, string $methodName, Arg $argNode): void
    {
        $expectExceptionMessageMethodCall = $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            $methodName,
            [$argNode]
        );

        $this->addNodeAfterNode($expectExceptionMessageMethodCall, $methodCallNode);
    }
}
