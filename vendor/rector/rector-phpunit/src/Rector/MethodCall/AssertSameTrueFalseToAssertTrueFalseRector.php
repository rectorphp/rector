<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeManipulator\ArgumentMover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector\AssertSameTrueFalseToAssertTrueFalseRectorTest
 */
final class AssertSameTrueFalseToAssertTrueFalseRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\PHPUnit\NodeManipulator\ArgumentMover
     */
    private $argumentMover;
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\PHPUnit\NodeManipulator\ArgumentMover $argumentMover, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->argumentMover = $argumentMover;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change $this->assertSame(true, ...) to assertTrue()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertSame', 'assertEqual', 'assertNotSame', 'assertNotEqual'])) {
            return null;
        }
        if ($this->valueResolver->isTrue($node->args[0]->value)) {
            $this->argumentMover->removeFirst($node);
            $node->name = new \PhpParser\Node\Identifier('assertTrue');
            return $node;
        }
        if ($this->valueResolver->isFalse($node->args[0]->value)) {
            $this->argumentMover->removeFirst($node);
            $node->name = new \PhpParser\Node\Identifier('assertFalse');
            return $node;
        }
        return null;
    }
}
