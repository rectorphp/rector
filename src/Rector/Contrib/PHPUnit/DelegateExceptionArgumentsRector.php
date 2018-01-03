<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Scoper\Scoper;

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
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;
    /**
     * @var Scoper
     */
    private $scoper;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, MethodCallNodeFactory $methodCallNodeFactory, Scoper $scoper)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->scoper = $scoper;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->scoper->isInClassOfPhpUnitTestCase($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isTypeAndMethods(
            $node,
            'PHPUnit\Framework\TestCase',
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

        $this->prependNewMethodCall(
            $methodCallNode,
            $this->oldToNewMethod[$oldMethodName],
            $methodCallNode->args[1]
        );
        unset($methodCallNode->args[1]);

        if (isset($methodCallNode->args[2])) {
            $this->prependNewMethodCall(
                $methodCallNode,
                'expectExceptionCode',
                $methodCallNode->args[2]
            );
            unset($methodCallNode->args[2]);
        }

        return $methodCallNode;
    }

    private function prependNewMethodCall(MethodCall $methodCallNode, string $methodName, Arg $argNode): void
    {
        $expectExceptionMessageMethodCall = $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            $methodName,
            [$argNode]
        );

        $this->prependNodeAfterNode($expectExceptionMessageMethodCall, $methodCallNode);
    }
}
