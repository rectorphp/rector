<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->setExpectedException(Exception::class, 'Message', 'CODE');
 *
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

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, MethodCallNodeFactory $methodCallNodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
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
        $oldMethodName = $methodCallNode->name->name;

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
