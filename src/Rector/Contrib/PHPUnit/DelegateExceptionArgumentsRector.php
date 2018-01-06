<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->setExpectedException(Exception::class, 'Message', 'CODE');
 *
 * After:
 * - $this->setExpectedException(Exception::class);
 * - $this->expectExceptionMessage('Message');
 * - $this->expectExceptionCode('CODE');
 */
final class DelegateExceptionArgumentsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewMethod = [
        'setExpectedException' => 'expectExceptionMessage',
        'setExpectedExceptionRegExp' => 'expectExceptionMessageRegExp',
    ];

    /**
     * @var string[]
     */
    private $phpUnitTestCaseClasses = [
        // PHPUnit 5-
        'PHPUnit_Framework_TestCase',
        // PHPUnit 6+
        'PHPUnit\Framework\TestCase',
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
        if (! $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            $this->phpUnitTestCaseClasses,
            array_keys($this->oldToNewMethod)
        )) {
            return false;
        }

        /** @var MethodCall $node */
        return isset($node->args[1]);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
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
