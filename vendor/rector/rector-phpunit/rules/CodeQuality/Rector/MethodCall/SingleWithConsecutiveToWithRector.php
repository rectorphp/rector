<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\PhpParser\Node\BetterNodeFinder;
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
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->getArgs()) !== 1) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        // skip as multiple unique values
        if ($firstArg->unpack) {
            return null;
        }
        // use simpler with()/willReturn() instead
        if ($this->isName($node->name, 'withConsecutive')) {
            $node->name = new Identifier('with');
        } else {
            $node->name = new Identifier('willReturn');
        }
        // has assert inside?
        $hasAssertInside = (bool) $this->betterNodeFinder->findFirst($firstArg->value, function (Node $node) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            return $this->isNames($node->name, ['equalTo', 'instanceOf']);
        });
        // replace $this->equalsTo() with direct value
        $this->traverseNodesWithCallable($firstArg->value, function (Node $node) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'equalTo')) {
                return null;
            }
            return $node->getArgs()[0]->value;
        });
        if ($hasAssertInside && $firstArg->value instanceof Array_) {
            $args = $this->nodeFactory->createArgs($firstArg->value->items);
        } else {
            $args = [new Arg($firstArg->value)];
        }
        $node->args = $args;
        return $node;
    }
}
