<?php

declare (strict_types=1);
namespace Rector\Carbon\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Carbon\Rector\FuncCall\DateFuncCallToCarbonRector\DateFuncCallToCarbonRectorTest
 */
final class DateFuncCallToCarbonRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert date() function call to Carbon::*()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $date = date('Y-m-d');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $date = \Carbon\Carbon::now()->format('Y-m-d')
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'date')) {
            return null;
        }
        if (\count($node->getArgs()) !== 1) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        if (!$firstArg->value instanceof String_) {
            return null;
        }
        // create now and format()
        $nowStaticCall = new StaticCall(new FullyQualified('Carbon\\Carbon'), 'now');
        return new MethodCall($nowStaticCall, 'format', [new Arg($firstArg->value)]);
    }
}
