<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Core\PhpParser\Node\Manipulator\MethodCallManipulator;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3120
 * "If, and only if, the expects() method is called on this stub to set up expectations then that stub becomes a mock."
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\CreateMockToCreateStubRector\CreateMockToCreateStubRectorTest
 */
final class CreateMockToCreateStubRector extends AbstractPHPUnitRector
{
    /**
     * @var MethodCallManipulator
     */
    private $methodCallManipulator;

    public function __construct(MethodCallManipulator $methodCallManipulator)
    {
        $this->methodCallManipulator = $methodCallManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces createMock() with createStub() when relevant', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase

class MyTest extends TestCase
{
    public function testItBehavesAsExpected(): void
    {
        $stub = $this->createMock(\Exception::class);
        $stub->method('getMessage')
            ->willReturn('a message');

        $mock = $this->createMock(\Exception::class);
        $mock->expects($this->once())
            ->method('getMessage')
            ->willReturn('a message');

        self::assertSame('a message', $stub->getMessage());
        self::assertSame('a message', $mock->getMessage());
    }
}
CODE_SAMPLE
                 ,
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase

class MyTest extends TestCase
{
    public function testItBehavesAsExpected(): void
    {
        $stub = $this->createStub(\Exception::class);
        $stub->method('getMessage')
            ->willReturn('a message');

        $mock = $this->createMock(\Exception::class);
        $mock->expects($this->once())
            ->method('getMessage')
            ->willReturn('a message');

        self::assertSame('a message', $stub->getMessage());
        self::assertSame('a message', $mock->getMessage());
    }
}
CODE_SAMPLE
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

        if (! $this->isName($node->name, 'createMock')) {
            return null;
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Assign) {
            return null;
        }

        $mockVariable = $parentNode->var;
        if (! $mockVariable instanceof Variable) {
            return null;
        }

        $methodCallNamesOnVariable = $this->methodCallManipulator->findMethodCallNamesOnVariable($mockVariable);
        if (in_array('expects', $methodCallNamesOnVariable, true)) {
            return null;
        }

        $node->name = new Identifier('createStub');

        return $node;
    }
}
