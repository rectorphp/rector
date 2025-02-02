<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit90\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit90\Rector\MethodCall\ReplaceAtMethodWithDesiredMatcherRector\ReplaceAtMethodWithDesiredMatcherRectorTest
 */
final class ReplaceAtMethodWithDesiredMatcherRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    private bool $hasChanged = \false;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace at() method call with desired matcher', [new CodeSample(<<<'CODE_SAMPLE'
$mock->expects($this->at(0))
    ->method('foo')
    ->willReturn('1');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$mock->expects($this->never())
    ->method('foo')
    ->willReturn('1');
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
     * @param  MethodCall  $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Expr\MethodCall
    {
        $this->hasChanged = \false;
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($node->var instanceof MethodCall && ($arg = $this->findAtMethodCall($node->var))) {
            $this->replaceWithDesiredMatcher($arg);
        }
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    private function findAtMethodCall(MethodCall $methodCall) : ?Arg
    {
        foreach ($methodCall->getArgs() as $arg) {
            if ($arg->value instanceof MethodCall && $arg->value->name instanceof Identifier && $arg->value->name->toString() === 'at') {
                return $arg;
            }
        }
        if ($methodCall->var instanceof MethodCall) {
            $this->findAtMethodCall($methodCall->var);
        }
        return null;
    }
    private function replaceWithDesiredMatcher(Arg $arg) : void
    {
        if (!$arg->value instanceof MethodCall) {
            return;
        }
        foreach ($arg->value->getArgs() as $item) {
            if ($item->value instanceof Int_) {
                $count = $item->value->value;
            }
        }
        if (!isset($count)) {
            return;
        }
        if ($count === 0) {
            $arg->value = new MethodCall($arg->value->var, 'never');
            $this->hasChanged = \true;
        } elseif ($count === 1) {
            $arg->value = new MethodCall($arg->value->var, 'once');
            $this->hasChanged = \true;
        } elseif ($count > 1) {
            $arg->value = new MethodCall($arg->value->var, 'exactly', [new Arg(new Int_($count))]);
            $this->hasChanged = \true;
        }
    }
}
