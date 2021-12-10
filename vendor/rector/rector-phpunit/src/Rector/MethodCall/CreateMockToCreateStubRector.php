<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Core\NodeManipulator\MethodCallManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3120
 * "If, and only if, the expects() method is called on this stub to set up expectations then that stub becomes a mock."
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\CreateMockToCreateStubRector\CreateMockToCreateStubRectorTest
 */
final class CreateMockToCreateStubRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\MethodCallManipulator
     */
    private $methodCallManipulator;
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\Core\NodeManipulator\MethodCallManipulator $methodCallManipulator, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->methodCallManipulator = $methodCallManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces createMock() with createStub() when relevant', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$this->isName($node->name, 'createMock')) {
            return null;
        }
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $mockVariable = $parentNode->var;
        if (!$mockVariable instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $methodCallNamesOnVariable = $this->methodCallManipulator->findMethodCallNamesOnVariable($mockVariable);
        if (\in_array('expects', $methodCallNamesOnVariable, \true)) {
            return null;
        }
        $node->name = new \PhpParser\Node\Identifier('createStub');
        return $node;
    }
}
