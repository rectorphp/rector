<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\CallAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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

    /**
     * @var CallAnalyzer
     */
    private $callAnalyzer;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        CallAnalyzer $callAnalyzer
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->callAnalyzer = $callAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns vague php-only method in PHPUnit TestCase to more specific', [
            new CodeSample(
                '$this->assertSame(10, count($anything), "message");',
                '$this->assertCount(10, $anything, "message");'
            ),
            new CodeSample(
                '$this->assertSame($value, {function}($anything), "message");',
                '$this->assert{function}($value, $anything, "message\");'
            ),
            new CodeSample(
                '$this->assertEquals($value, {function}($anything), "message");',
                '$this->assert{function}($value, $anything, "message\");'
            ),
            new CodeSample(
                '$this->assertNotSame($value, {function}($anything), "message");',
                '$this->assertNot{function}($value, $anything, "message")'
            ),
            new CodeSample(
                '$this->assertNotEquals($value, {function}($anything), "message");',
                '$this->assertNot{function}($value, $anything, "message")'
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
        if (! $this->isInTestClass($methodCallNode)) {
            return null;
        }
        if (! $this->methodCallAnalyzer->isMethods(
            $methodCallNode,
            ['assertSame', 'assertNotSame', 'assertEquals', 'assertNotEquals']
        )) {
            return null;
        }
        /** @var FuncCall $secondArgumentValue */
        $secondArgumentValue = $methodCallNode->args[1]->value;

        $resolvedFuncCallName = $this->callAnalyzer->resolveName($secondArgumentValue);
        if ($resolvedFuncCallName === null) {
            return null;
        }

        $this->activeFuncCallName = $resolvedFuncCallName;
        if (! isset($this->defaultOldToNewMethods[$this->activeFuncCallName])) {
            return null;
        }

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
}
