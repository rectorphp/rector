<?php

declare(strict_types=1);

namespace Rector\Laravel\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/laravel/framework/pull/25315
 * @see https://laracasts.com/discuss/channels/eloquent/laravel-eloquent-where-date-is-equal-or-smaller-than-datetime
 *
 * @see \Rector\Laravel\Tests\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector\ChangeQueryWhereDateValueWithCarbonRectorTest
 */
final class ChangeQueryWhereDateValueWithCarbonRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Add parent::boot(); call to boot() class method in child of Illuminate\Database\Eloquent\Model',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Illuminate\Database\Query\Builder;

final class SomeClass
{
    public function run(Builder $query)
    {
        $query->whereDate(
            'created_at',
            '<',
            Carbon::now()
        );
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Illuminate\Database\Query\Builder;

final class SomeClass
{
    public function run(Builder $query)
    {
        $query->whereDate(
            'created_at',
            '<=',
            Carbon::now()
        );
    }
}
CODE_SAMPLE

                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $argValue = $this->matchWhereDateThirdArgValue($node);
        if ($argValue === null) {
            return null;
        }

        // is just made with static call?
        if ($argValue instanceof StaticCall) {
            /** @var StaticCall $argValue */
            if ($this->isStaticCallNamed($argValue, 'Carbon\Carbon', 'now')) {
                // now!
                // 1. extract assign
                $dateTimeVariable = new Variable('dateTime');
                $assign = new Assign($dateTimeVariable, $argValue);
                $this->addNodeBeforeNode($assign, $node);

                $node->args[2]->value = $dateTimeVariable;

                // update assign ">" → ">="
                $originalCompareSignExpr = $node->args[1]->value;
                $node->args[1]->value = $this->createCompareSignExpr($originalCompareSignExpr);

                // 2. add "whereTime()" time call
                $whereTimeMethodCall = $this->createWhereTimeMethodCall(
                    $node,
                    $originalCompareSignExpr,
                    $dateTimeVariable
                );
                $this->addNodeAfterNode($whereTimeMethodCall, $node);
            }

            return $node;
        }

        if ($argValue instanceof Variable) {
            $dateTimeVariable = $argValue;

            $originalCompareSignExpr = clone $node->args[1]->value;
            $compareSignExpr = $node->args[1]->value;
            $node->args[1]->value = $this->createCompareSignExpr($compareSignExpr);

            // 2. add "whereTime()" time call
            $whereTimeMethodCall = $this->createWhereTimeMethodCall($node, $originalCompareSignExpr, $dateTimeVariable);
            $this->addNodeAfterNode($whereTimeMethodCall, $node);
        }

        return null;
    }

    private function matchWhereDateThirdArgValue(MethodCall $methodCall): ?Expr
    {
        if (! $this->isOnClassMethodCall($methodCall, 'Illuminate\Database\Query\Builder', 'whereDate')) {
            return null;
        }

        if (! isset($methodCall->args[2])) {
            return null;
        }

        $argValue = $methodCall->args[2]->value;
        if (! $this->isObjectType($argValue, 'DateTimeInterface')) {
            return null;
        }

        // nothing to change
        if ($this->isStaticCallNamed($argValue, 'Carbon\Carbon', 'today')) {
            return null;
        }

        if ($this->isValues($methodCall->args[1]->value, ['>=', '<='])) {
            return null;
        }

        return $argValue;
    }

    private function createCompareSignExpr(Expr $expr): Expr
    {
        if (! $expr instanceof String_) {
            return $expr;
        }

        if ($expr->value === '<') {
            return new String_('<=');
        }

        if ($expr->value === '>') {
            return new String_('>=');
        }

        return $expr;
    }

    private function createWhereTimeMethodCall(
        MethodCall $methodCall,
        Expr $compareSignExpr,
        Variable $dateTimeVariable
    ): MethodCall {
        $whereTimeArgs = [$methodCall->args[0], new Arg($compareSignExpr), new Arg($dateTimeVariable)];

        return new MethodCall($methodCall->var, 'whereTime', $whereTimeArgs);
    }
}
