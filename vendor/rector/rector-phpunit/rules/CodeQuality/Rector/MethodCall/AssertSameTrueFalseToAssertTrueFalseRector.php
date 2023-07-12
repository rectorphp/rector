<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\ArgumentMover;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector\AssertSameTrueFalseToAssertTrueFalseRectorTest
 */
final class AssertSameTrueFalseToAssertTrueFalseRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\ArgumentMover
     */
    private $argumentMover;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(ArgumentMover $argumentMover, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->argumentMover = $argumentMover;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $this->assertSame(true, ...) to assertTrue()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertSame', 'assertEqual', 'assertNotSame', 'assertNotEqual'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        if ($this->valueResolver->isTrue($firstArg->value)) {
            $this->argumentMover->removeFirstArg($node);
            $node->name = new Identifier('assertTrue');
            return $node;
        }
        if ($this->valueResolver->isFalse($firstArg->value)) {
            $this->argumentMover->removeFirstArg($node);
            $node->name = new Identifier('assertFalse');
            return $node;
        }
        return null;
    }
}
