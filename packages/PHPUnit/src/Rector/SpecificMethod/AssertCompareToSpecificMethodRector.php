<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertCompareToSpecificMethodRector\AssertCompareToSpecificMethodRectorTest
 */
final class AssertCompareToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]|false[][]
     */
    private $defaultOldToNewMethods = [
        'count' => ['assertCount', 'assertNotCount'],
        'sizeof' => ['assertCount', 'assertNotCount'],
        'iterator_count' => ['assertCount', 'assertNotCount'],
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
                '$this->assert{function}($value, $anything, "message");'
            ),
            new CodeSample(
                '$this->assertEquals($value, {function}($anything), "message");',
                '$this->assert{function}($value, $anything, "message");'
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
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isPHPUnitMethodNames($node, ['assertSame', 'assertNotSame', 'assertEquals', 'assertNotEquals'])) {
            return null;
        }

        if (! isset($node->args[1])) {
            return null;
        }

        $secondArgumentValue = $node->args[1]->value;
        if (! $secondArgumentValue instanceof FuncCall) {
            return null;
        }

        $name = $this->getName($secondArgumentValue);
        if ($name === null) {
            return null;
        }

        if (! isset($this->defaultOldToNewMethods[$name])) {
            return null;
        }

        $this->renameMethod($node, $name);
        $this->moveFunctionArgumentsUp($node);

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function renameMethod(Node $node, string $funcName): void
    {
        /** @var string $oldMethodName */
        $oldMethodName = $this->getName($node);

        [$trueMethodName, $falseMethodName] = $this->defaultOldToNewMethods[$funcName];

        if (in_array($oldMethodName, ['assertSame', 'assertEquals'], true) && $trueMethodName) {
            $node->name = new Identifier($trueMethodName);
        } elseif (in_array($oldMethodName, ['assertNotSame', 'assertNotEquals'], true) && $falseMethodName) {
            $node->name = new Identifier($falseMethodName);
        }
    }

    /**
     * Handles custom error messages to not be overwrite by function with multiple args.
     * @param StaticCall|MethodCall $node
     */
    private function moveFunctionArgumentsUp(Node $node): void
    {
        /** @var FuncCall $secondArgument */
        $secondArgument = $node->args[1]->value;
        $node->args[1] = $secondArgument->args[0];
    }
}
