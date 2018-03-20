<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\Rector\AbstractPHPUnitRector;

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
final class AssertCompareToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]|false[][]
     */
    private $defaultOldToNewMethods = [
        'count' => ['assertCount', 'assertNotCount'],
        'sizeof' => ['assertCount', 'assertNotCount'],
        'gettype' => ['assertInternalType', 'assertNotInternalType'],
        'get_class' => ['assertInstanceOf', 'assertNotInstanceOf'],
    ];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var string
     */
    private $activeFuncCallName;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, IdentifierRenamer $identifierRenamer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethods(
            $node,
            ['assertSame', 'assertNotSame', 'assertEquals', 'assertNotEquals']
        )) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        /** @var FuncCall $secondArgumentValue */
        $secondArgumentValue = $methodCallNode->args[1]->value;

        if (! $this->isNamedFunction($secondArgumentValue)) {
            return false;
        }

        $methodName = $secondArgumentValue->name->toString();
        $this->activeFuncCallName = $methodName;

        return isset($this->defaultOldToNewMethods[$methodName]);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->renameMethod($methodCallNode);
        $this->moveFunctionArgumentsUp($methodCallNode);

        return $methodCallNode;
    }

    private function renameMethod(MethodCall $methodCallNode): void
    {
        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNode->name;
        $oldMethodName = $identifierNode->toString();

        [$trueMethodName, $falseMethodName] = $this->defaultOldToNewMethods[$this->activeFuncCallName];

        if (in_array($oldMethodName, ['assertSame', 'assertEquals'], true) && $trueMethodName) {
            $this->identifierRenamer->renameNode($methodCallNode, $trueMethodName);
        } elseif (in_array($oldMethodName, ['assertNotSame', 'assertNotEquals'], true) && $falseMethodName) {
            $this->identifierRenamer->renameNode($methodCallNode, $falseMethodName);
        }
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

    private function isNamedFunction(Expr $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        $functionName = $node->name;
        return $functionName instanceof Name;
    }
}
