<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/laravel/framework/pull/25315
 * @changelog https://laracasts.com/discuss/channels/eloquent/laravel-eloquent-where-date-is-equal-or-smaller-than-datetime
 *
 * @see \Rector\Laravel\Tests\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector\ChangeQueryWhereDateValueWithCarbonRectorTest
 */
final class ChangeQueryWhereDateValueWithCarbonRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(NodesToAddCollector $nodesToAddCollector)
    {
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add parent::boot(); call to boot() class method in child of Illuminate\\Database\\Eloquent\\Model', [new CodeSample(<<<'CODE_SAMPLE'
use Illuminate\Database\Query\Builder;

final class SomeClass
{
    public function run(Builder $query)
    {
        $query->whereDate('created_at', '<', Carbon::now());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Illuminate\Database\Query\Builder;

final class SomeClass
{
    public function run(Builder $query)
    {
        $dateTime = Carbon::now();
        $query->whereDate('created_at', '<=', $dateTime);
        $query->whereTime('created_at', '<=', $dateTime);
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
        $expr = $this->matchWhereDateThirdArgValue($node);
        if (!$expr instanceof Expr) {
            return null;
        }
        // is just made with static call?
        if ($expr instanceof StaticCall || $expr instanceof MethodCall) {
            // now!
            // 1. extract assign
            $dateTimeVariable = new Variable('dateTime');
            $assign = new Assign($dateTimeVariable, $expr);
            $this->nodesToAddCollector->addNodeBeforeNode($assign, $node);
            if (!$node->args[2] instanceof Arg) {
                return null;
            }
            $node->args[2]->value = $dateTimeVariable;
            if (!$node->args[1] instanceof Arg) {
                return null;
            }
            // update assign ">" → ">="
            $this->changeCompareSignExpr($node->args[1]);
            // 2. add "whereTime()" time call
            $whereTimeMethodCall = $this->createWhereTimeMethodCall($node, $dateTimeVariable);
            $this->nodesToAddCollector->addNodeAfterNode($whereTimeMethodCall, $node);
            return $node;
        }
        if ($expr instanceof Variable && $node->args[1] instanceof Arg) {
            $dateTimeVariable = $expr;
            $this->changeCompareSignExpr($node->args[1]);
            // 2. add "whereTime()" time call
            $whereTimeMethodCall = $this->createWhereTimeMethodCall($node, $dateTimeVariable);
            $this->nodesToAddCollector->addNodeAfterNode($whereTimeMethodCall, $node);
        }
        return null;
    }
    private function matchWhereDateThirdArgValue(MethodCall $methodCall) : ?Expr
    {
        if (!$this->isObjectType($methodCall->var, new ObjectType('Illuminate\\Database\\Query\\Builder'))) {
            return null;
        }
        if (!$this->isName($methodCall->name, 'whereDate')) {
            return null;
        }
        if (!isset($methodCall->args[2])) {
            return null;
        }
        if (!$methodCall->args[2] instanceof Arg) {
            return null;
        }
        $argValue = $methodCall->args[2]->value;
        if (!$this->isObjectType($argValue, new ObjectType('DateTimeInterface'))) {
            return null;
        }
        // nothing to change
        if ($this->isCarbonTodayStaticCall($argValue)) {
            return null;
        }
        if (!$methodCall->args[1] instanceof Arg) {
            return null;
        }
        if ($this->valueResolver->isValues($methodCall->args[1]->value, ['>=', '<='])) {
            return null;
        }
        return $argValue;
    }
    private function changeCompareSignExpr(Arg $arg) : void
    {
        if (!$arg->value instanceof String_) {
            return;
        }
        $string = $arg->value;
        if ($string->value === '<') {
            $string->value = '<=';
        }
        if ($string->value === '>') {
            $string->value = '>=';
        }
    }
    private function createWhereTimeMethodCall(MethodCall $methodCall, Variable $dateTimeVariable) : MethodCall
    {
        $whereTimeArgs = [$methodCall->args[0], $methodCall->args[1], new Arg($dateTimeVariable)];
        return new MethodCall($methodCall->var, 'whereTime', $whereTimeArgs);
    }
    private function isCarbonTodayStaticCall(Expr $expr) : bool
    {
        if (!$expr instanceof StaticCall) {
            return \false;
        }
        $carbonObjectType = new ObjectType('Carbon\\Carbon');
        $callerType = $this->nodeTypeResolver->getType($expr->class);
        if (!$carbonObjectType->isSuperTypeOf($callerType)->yes()) {
            return \false;
        }
        return $this->isName($expr->name, 'today');
    }
}
