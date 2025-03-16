<?php

declare (strict_types=1);
namespace Rector\Doctrine\Orm30\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/orm/commit/4d73e3ce7801d3bf3254257332e903d8ecea4096
 */
final class CastDoctrineExprToStringRector extends AbstractRector
{
    /**
     * @var array<string>
     */
    private array $targetMethods = ['like', 'notLike', 'eq', 'neq', 'lt', 'lte', 'gt', 'gte', 'between', 'in', 'notIn', 'isMemberOf', 'isInstanceOf'];
    /**
     * @var array<string>
     */
    private array $exprFuncMethods = ['lower', 'upper', 'length', 'trim', 'avg', 'max', 'min', 'count', 'countDistinct', 'exists', 'all', 'some', 'any', 'not', 'abs', 'sqrt'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Casts Doctrine Expr\\x to string where necessary.', [new CodeSample(<<<'CODE_SAMPLE'
$statements->add(
    $builder->expr()->like(
        $builder->expr()->lower($column),
        $builder->expr()->lower($builder->expr()->literal('%'.$like.'%'))
    )
);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$statements->add(
    $builder->expr()->like(
        (string) $builder->expr()->lower($column),
        (string) $builder->expr()->lower($builder->expr()->literal('%'.$like.'%'))
    )
);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    public function refactor(Node $node) : ?Node
    {
        if (!$node instanceof MethodCall) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Doctrine\\ORM\\Query\\Expr'))) {
            return null;
        }
        if (!\in_array($this->getName($node->name), $this->targetMethods, \true)) {
            return null;
        }
        // Iterate through method arguments and cast `Expr\Func` calls to string
        $hasChanged = \false;
        foreach ($node->args as $arg) {
            if (!$arg instanceof Arg) {
                return null;
            }
            if ($arg->value instanceof MethodCall && $this->isObjectType($arg->value->var, new ObjectType('Doctrine\\ORM\\Query\\Expr')) && \in_array($this->getName($arg->value->name), $this->exprFuncMethods, \true)) {
                $arg->value = new String_($arg->value);
                $hasChanged = \true;
            }
        }
        return $hasChanged ? $node : null;
    }
}
