<?php

declare (strict_types=1);
namespace Rector\Carbon\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Carbon\Rector\FuncCall\DateFuncCallToCarbonRector\DateFuncCallToCarbonRectorTest
 */
final class TimeFuncCallToCarbonRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert time() function call to Carbon::now()->timestamp', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $time = time();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $time = \Carbon\Carbon::now()->timestamp;
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
        if (!$this->isName($node->name, 'time')) {
            return null;
        }
        $firstClassCallable = $node->isFirstClassCallable();
        if (!$firstClassCallable && \count($node->getArgs()) !== 0) {
            return null;
        }
        // create now and format()
        $nowStaticCall = new StaticCall(new FullyQualified('Carbon\\Carbon'), 'now');
        $propertyFetch = new PropertyFetch($nowStaticCall, 'timestamp');
        if ($firstClassCallable) {
            return new ArrowFunction(['static' => \true, 'expr' => $propertyFetch]);
        }
        return $propertyFetch;
    }
}
