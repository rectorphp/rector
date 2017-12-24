<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use Nette\Utils\Arrays;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\MethodNameChanger;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->assertSame(10, count($anything), 'message');
 * - $this->assertSame($value, {function}($anything), 'message');
 * - $this->assertNotSame($value, {function}($anything), 'message');
 * - $this->assertEquals($value, {function}($anything), 'message');
 * - $this->assertNotEquals($value, {function}($anything), 'message');
 *
 * After:
 * - $this->assertCount(10, $anything, 'message');
 * - $this->assert{function}($value, $anything, 'message');
 * - $this->assertNot{function}($value, $anything, 'message');
 */
final class AssertCompareToSpecificMethodRector extends AbstractRector
{
    /**
     * @var string[][]|false[][]
     */
    private $defaultOldToNewMethods = [
        'count' => ['assertCount', 'assertNotCount'],
        'sizeof' => ['assertCount', 'assertNotCount'],
        'gettype' => ['assertInternalType', 'assertNotInternalType'],
    ];

    /**
     * @var string[][]|string[]
     */
    private $acceptedAssertionMethods = [
        'trueMethodNames' => ['assertSame', 'assertEquals'],
        'falseMethodNames' => ['assertNotSame', 'assertNotEquals'],
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
     * @var string|null
     */
    private $activeFuncCallName;

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
            Arrays::flatten($this->acceptedAssertionMethods)
        )) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $firstArgumentValue instanceof LNumber &&
            ! $firstArgumentValue instanceof String_
        ) {
            return false;
        }

        $secondArgumentValue = $methodCallNode->args[1]->value;

        if (! $secondArgumentValue instanceof FuncCall) {
            return false;
        }

        $funcCallName = $secondArgumentValue->name->toString();
        if (! isset($this->defaultOldToNewMethods[$funcCallName])) {
            return false;
        }

        $this->activeFuncCallName = $funcCallName;

        return true;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->methodNameChanger->renameNode(
            $methodCallNode,
            $this->defaultOldToNewMethods,
            $this->acceptedAssertionMethods,
            $this->activeFuncCallName
        );
        $this->moveFunctionArgumentsUp($methodCallNode);

        return $methodCallNode;
    }

    /**
     * Handles custom error messages to not be overwrite by function with multiple args.
     */
    private function moveFunctionArgumentsUp(MethodCall $methodCallNode): void
    {
        /** @var FuncCall $secondArgument */
        $secondArgument = $methodCallNode->args[1]->value;
        $methodCallNode->args[1] = $secondArgument->args[0];
    }
}
