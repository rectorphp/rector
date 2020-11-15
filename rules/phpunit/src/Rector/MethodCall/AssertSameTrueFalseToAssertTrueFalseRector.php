<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector\AssertSameTrueFalseToAssertTrueFalseRectorTest
 */
final class AssertSameTrueFalseToAssertTrueFalseRector extends AbstractPHPUnitRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change $this->assertSame(true, ...) to assertTrue()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $value = (bool) mt_rand(0, 1);
        $this->assertSame(true, $value);
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $value = (bool) mt_rand(0, 1);
        $this->assertTrue($value);
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
        if (! $this->isPHPUnitMethodNames($node, ['assertSame', 'assertEqual', 'assertNotSame', 'assertNotEqual'])) {
            return null;
        }

        if ($this->isTrue($node->args[0]->value)) {
            $this->removeFirstArgument($node);

            $node->name = new Identifier('assertTrue');

            return $node;
        }

        if ($this->isFalse($node->args[0]->value)) {
            $this->removeFirstArgument($node);

            $node->name = new Identifier('assertFalse');
            return $node;
        }

        return null;
    }

    private function removeFirstArgument(MethodCall $methodCall): void
    {
        $args = $methodCall->args;
        array_shift($args);

        $methodCall->args = $args;
    }
}
