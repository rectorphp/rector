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
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/laravel/framework/pull/25315
 * @see https://laracasts.com/discuss/channels/eloquent/laravel-eloquent-where-date-is-equal-or-smaller-than-datetime
 *
 * @see \Rector\Laravel\Tests\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector\ChangeQueryWhereDateValueWithCarbonRectorTest
 */
final class ChangeQueryWhereDateValueWithCarbonRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add parent::boot(); call to boot() class method in child of Illuminate\\Database\\Eloquent\\Model', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $argValue = $this->matchWhereDateThirdArgValue($node);
        if (!$argValue instanceof \PhpParser\Node\Expr) {
            return null;
        }
        // is just made with static call?
        if ($argValue instanceof \PhpParser\Node\Expr\StaticCall || $argValue instanceof \PhpParser\Node\Expr\MethodCall) {
            // now!
            // 1. extract assign
            $dateTimeVariable = new \PhpParser\Node\Expr\Variable('dateTime');
            $assign = new \PhpParser\Node\Expr\Assign($dateTimeVariable, $argValue);
            $this->nodesToAddCollector->addNodeBeforeNode($assign, $node);
            if (!$node->args[2] instanceof \PhpParser\Node\Arg) {
                return null;
            }
            $node->args[2]->value = $dateTimeVariable;
            if (!$node->args[1] instanceof \PhpParser\Node\Arg) {
                return null;
            }
            // update assign ">" → ">="
            $this->changeCompareSignExpr($node->args[1]);
            // 2. add "whereTime()" time call
            $whereTimeMethodCall = $this->createWhereTimeMethodCall($node, $dateTimeVariable);
            $this->nodesToAddCollector->addNodeAfterNode($whereTimeMethodCall, $node);
            return $node;
        }
        if ($argValue instanceof \PhpParser\Node\Expr\Variable && $node->args[1] instanceof \PhpParser\Node\Arg) {
            $dateTimeVariable = $argValue;
            $this->changeCompareSignExpr($node->args[1]);
            // 2. add "whereTime()" time call
            $whereTimeMethodCall = $this->createWhereTimeMethodCall($node, $dateTimeVariable);
            $this->nodesToAddCollector->addNodeAfterNode($whereTimeMethodCall, $node);
        }
        return null;
    }
    private function matchWhereDateThirdArgValue(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr
    {
        if (!$this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType('Illuminate\\Database\\Query\\Builder'))) {
            return null;
        }
        if (!$this->isName($methodCall->name, 'whereDate')) {
            return null;
        }
        if (!isset($methodCall->args[2])) {
            return null;
        }
        if (!$methodCall->args[2] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $argValue = $methodCall->args[2]->value;
        if (!$this->isObjectType($argValue, new \PHPStan\Type\ObjectType('DateTimeInterface'))) {
            return null;
        }
        // nothing to change
        if ($this->isCarbonTodayStaticCall($argValue)) {
            return null;
        }
        if (!$methodCall->args[1] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if ($this->valueResolver->isValues($methodCall->args[1]->value, ['>=', '<='])) {
            return null;
        }
        return $argValue;
    }
    private function changeCompareSignExpr(\PhpParser\Node\Arg $arg) : void
    {
        if (!$arg->value instanceof \PhpParser\Node\Scalar\String_) {
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
    private function createWhereTimeMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\Variable $dateTimeVariable) : \PhpParser\Node\Expr\MethodCall
    {
        $whereTimeArgs = [$methodCall->args[0], $methodCall->args[1], new \PhpParser\Node\Arg($dateTimeVariable)];
        return new \PhpParser\Node\Expr\MethodCall($methodCall->var, 'whereTime', $whereTimeArgs);
    }
    private function isCarbonTodayStaticCall(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\StaticCall) {
            return \false;
        }
        $carbonObjectType = new \PHPStan\Type\ObjectType('Carbon\\Carbon');
        $callerType = $this->nodeTypeResolver->getType($expr->class);
        if (!$carbonObjectType->isSuperTypeOf($callerType)->yes()) {
            return \false;
        }
        return $this->isName($expr->name, 'today');
    }
}
