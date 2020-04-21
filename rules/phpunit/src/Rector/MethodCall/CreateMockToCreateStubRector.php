<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Core\PhpParser\Node\Manipulator\MethodCallManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3120
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\CreateMockToCreateStubRector\CreateMockToCreateStubRectorTest
 */
final class CreateMockToCreateStubRector extends AbstractRector
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
                <<<'PHP'
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
PHP
                 ,
                <<<'PHP'
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
PHP
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
