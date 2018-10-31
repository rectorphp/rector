<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
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
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isNames($node, ['assertSame', 'assertNotSame', 'assertEquals', 'assertNotEquals'])) {
            return null;
        }

        if (! isset($node->args[1])) {
            return null;
        }

        $secondArgumentValue = $node->args[1]->value;
        if (! $secondArgumentValue instanceof FuncCall) {
            return null;
        }

        if (! $this->isNames($secondArgumentValue, array_keys($this->defaultOldToNewMethods))) {
            return null;
        }

        $this->renameMethod($node, $this->getName($secondArgumentValue));
        $this->moveFunctionArgumentsUp($node);

        return $node;
    }

    private function renameMethod(MethodCall $methodCallNode, string $funcName): void
    {
        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNode->name;
        $oldMethodName = $identifierNode->toString();

        [$trueMethodName, $falseMethodName] = $this->defaultOldToNewMethods[$funcName];

        if (in_array($oldMethodName, ['assertSame', 'assertEquals'], true) && $trueMethodName) {
            $methodCallNode->name = new Identifier($trueMethodName);
        } elseif (in_array($oldMethodName, ['assertNotSame', 'assertNotEquals'], true) && $falseMethodName) {
            $methodCallNode->name = new Identifier($falseMethodName);
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
