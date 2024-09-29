<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\SingleWithConsecutiveToWithRector\SingleWithConsecutiveToWithRectorTest
 */
final class SingleWithConsecutiveToWithRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change single-value withConsecutive() to with() call, willReturnOnConsecutiveCalls() to willReturn() call', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function run()
    {
        $this->personServiceMock->expects($this->exactly(3))
            ->method('prepare')
            ->withConsecutive(
                [1],
            );
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function run()
    {
        $this->personServiceMock->expects($this->exactly(3))
            ->method('prepare')
            ->with([1]);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<MethodCall>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$this->isNames($node->name, ['withConsecutive', 'willReturnOnConsecutiveCalls'])) {
            return null;
        }
        if (\count($node->getArgs()) !== 1) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        // use simpler with()/willReturn() instead
        if ($this->isName($node->name, 'withConsecutive')) {
            $node->name = new Identifier('with');
        } else {
            $node->name = new Identifier('willReturn');
        }
        $node->args = [new Arg($firstArg->value)];
        return $node;
    }
}
