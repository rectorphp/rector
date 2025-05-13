<?php

declare (strict_types=1);
namespace Rector\Carbon\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Carbon\NodeFactory\CarbonCallFactory;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Carbon\Rector\MethodCall\DateTimeMethodCallToCarbonRector\DateTimeMethodCallToCarbonRectorTest
 */
final class DateTimeMethodCallToCarbonRector extends AbstractRector
{
    /**
     * @readonly
     */
    private CarbonCallFactory $carbonCallFactory;
    public function __construct(CarbonCallFactory $carbonCallFactory)
    {
        $this->carbonCallFactory = $carbonCallFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert `new DateTime()` with a method call to `Carbon::*()`', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $date = (new \DateTime('today +20 day'))->format('Y-m-d');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $date = \Carbon\Carbon::today()->addDays(20)->format('Y-m-d')
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
        if (!$node->var instanceof New_) {
            return null;
        }
        $new = $node->var;
        if (!$new->class instanceof Name) {
            return null;
        }
        if (!$this->isName($new->class, 'DateTime') && !$this->isName($new->class, 'DateTimeImmutable')) {
            return null;
        }
        if ($new->isFirstClassCallable()) {
            return null;
        }
        if (\count($new->getArgs()) !== 1) {
            // @todo handle in separate static call
            return null;
        }
        $firstArg = $new->getArgs()[0];
        if (!$firstArg->value instanceof String_) {
            return null;
        }
        if ($this->isName($new->class, 'DateTime')) {
            $carbonFullyQualified = new FullyQualified('Carbon\\Carbon');
        } else {
            $carbonFullyQualified = new FullyQualified('Carbon\\CarbonImmutable');
        }
        $carbonCall = $this->carbonCallFactory->createFromDateTimeString($carbonFullyQualified, $firstArg->value);
        $node->var = $carbonCall;
        return $node;
    }
}
