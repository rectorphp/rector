<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\MethodNameChanger;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->assertSame(null, $anything);
 * - $this->assertNotSame(false, $anything);
 *
 * After:
 * - $this->assertNull($anything);
 * - $this->assertNotFalse($anything);
 */
final class AssertSameBoolNullToSpecificMethodRector extends AbstractRector
{
    /**
     * @var string[][]|false[][]
     */
    private $constValueToNewMethodNames = [
        'null' => ['assertNull', 'assertNotNull'],
        'true' => ['assertTrue', 'assertNotTrue'],
        'false' => ['assertFalse', 'assertNotFalse'],
    ];

    /**
     * @var string[][]|string[]
     */
    private $acceptedAssertionMethods = [
        'trueMethodNames' => ['assertSame'],
        'falseMethodNames' => ['assertNotSame'],
    ];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodNameChanger
     */
    private $methodNameChanger;

    /**
     * @var string
     */
    private $constantName;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, MethodNameChanger $methodNameChanger)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodNameChanger = $methodNameChanger;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'],
            ['assertSame', 'assertNotSame']
        )) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $firstArgumentValue instanceof ConstFetch) {
            return false;
        }

        $this->constantName = $firstArgumentValue->name->toString();

        return isset($this->constValueToNewMethodNames[$this->constantName]);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->methodNameChanger->renameNode(
            $methodCallNode,
            $this->constValueToNewMethodNames,
            $this->acceptedAssertionMethods,
            $this->constantName
        );
        $this->moveArguments($methodCallNode);

        return $methodCallNode;
    }

    private function moveArguments(MethodCall $methodCallNode): void
    {
        $methodArguments = $methodCallNode->args;
        array_shift($methodArguments);

        $methodCallNode->args = $methodArguments;
    }
}
