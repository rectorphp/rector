<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit90\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
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
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$node->var instanceof MethodCall) {
            return null;
        }
        $arg = $this->findAtMethodCall($node->var);
        if (!$arg instanceof Arg) {
            return null;
        }
        if (!$arg->value instanceof MethodCall) {
            return null;
        }
        $count = null;
        foreach ($arg->value->getArgs() as $item) {
            if ($item->value instanceof Int_) {
                $count = $item->value->value;
            }
        }
        if (!isset($count)) {
            return null;
        }
        if ($count === 0) {
            $arg->value = new MethodCall($arg->value->var, 'never');
            return $node;
        }
        if ($count === 1) {
            $arg->value = new MethodCall($arg->value->var, 'once');
            return $node;
        }
        if ($count > 1) {
            $arg->value = new MethodCall($arg->value->var, 'exactly', [new Arg(new Int_($count))]);
            return $node;
        }
        return null;
    }
    private function findAtMethodCall(MethodCall $methodCall): ?Arg
    {
        foreach ($methodCall->getArgs() as $arg) {
            $argExpr = $arg->value;
            if ($argExpr instanceof MethodCall && $this->isName($argExpr->name, 'at')) {
                return $arg;
            }
        }
        if ($methodCall->var instanceof MethodCall) {
            $this->findAtMethodCall($methodCall->var);
        }
        return null;
    }
}
