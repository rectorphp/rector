<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Covers ref. https://github.com/RectorPHP/Rector/issues/79
 */
final class ExceptionRector extends AbstractRector
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

    public function isCandidate(Node $node): bool
    {
        return $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'],
            array_keys($this->oldToNewMethod)
        );
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $oldMethodName = $methodCallNode->name->name;
        $methodCallNode->name->name = 'expectException';

        if (! isset($methodCallNode->args[1])) {
            return $methodCallNode;
        }

        $secondArgument = $methodCallNode->args[1];
        unset($methodCallNode->args[1]);

        $expectExceptionMessageMethodCall = $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            $this->oldToNewMethod[$oldMethodName],
            [$secondArgument]
        );

        $this->prependNodeAfterNode($expectExceptionMessageMethodCall, $methodCallNode);

        return $methodCallNode;
    }
}
