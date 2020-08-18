<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/5.7.0/src/Framework/TestCase.php#L1623
 * @see https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L1452
 *
 * @see \Rector\PHPUnit\Tests\Rector\StaticCall\GetMockRector\GetMockRectorTest
 */
final class GetMockRector extends AbstractPHPUnitRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns getMock*() methods to createMock()', [
            new CodeSample('$this->getMock("Class");', '$this->createMock("Class");'),
            new CodeSample(
                '$this->getMockWithoutInvokingTheOriginalConstructor("Class");',
                '$this->createMock("Class");'
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
        if (! $this->isPHPUnitMethodNames($node, ['getMock', 'getMockWithoutInvokingTheOriginalConstructor'])) {
            return null;
        }

        if ($node instanceof MethodCall && $node->var instanceof MethodCall) {
            return null;
        }

        // narrow args to one
        if (count($node->args) > 1) {
            $node->args = [$node->args[0]];
        }

        $node->name = new Identifier('createMock');

        return $node;
    }
}
