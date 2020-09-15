<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/lmc-eu/steward/pull/187/files#diff-c7e8c65e59b8b4ff8b54325814d4ba55L80
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\GetMockBuilderGetMockToCreateMockRectorTest
 */
final class GetMockBuilderGetMockToCreateMockRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove getMockBuilder() to createMock()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->getMockBuilder('SomeClass')
           ->disableOriginalConstructor()
           ->getMock();
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->createMock('SomeClass');
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
        if (! $this->isName($node->name, 'getMock')) {
            return null;
        }

        if (! $node->var instanceof MethodCall) {
            return null;
        }

        $getMockBuilderMethodCall = $this->isName(
            $node->var->name,
            'disableOriginalConstructor'
        ) ? $node->var->var : $node->var;

        /** @var MethodCall|null $getMockBuilderMethodCall */
        if ($getMockBuilderMethodCall === null) {
            return null;
        }

        if (! $this->isName($getMockBuilderMethodCall->name, 'getMockBuilder')) {
            return null;
        }

        $args = $getMockBuilderMethodCall->args;
        $thisVariable = $getMockBuilderMethodCall->var;

        return new MethodCall($thisVariable, 'createMock', $args);
    }
}
