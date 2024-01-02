<?php

declare (strict_types=1);
namespace Rector\Doctrine\Dbal211\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/doctrine/dbal/pull/3853
 * @changelog https://github.com/doctrine/dbal/issues/3837
 *
 * @see \Rector\Doctrine\Tests\Dbal211\Rector\MethodCall\ExtractArrayArgOnQueryBuilderSelectRector\ExtractArrayArgOnQueryBuilderSelectRectorTest
 */
final class ExtractArrayArgOnQueryBuilderSelectRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Extract array arg on QueryBuilder select, addSelect, groupBy, addGroupBy', [new CodeSample(<<<'CODE_SAMPLE'
function query(\Doctrine\DBAL\Query\QueryBuilder $queryBuilder)
{
    $query = $queryBuilder->select(['u.id', 'p.id']);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function query(\Doctrine\DBAL\Query\QueryBuilder $queryBuilder)
{
    $query = $queryBuilder->select('u.id', 'p.id');
}
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?MethodCall
    {
        $varType = $this->nodeTypeResolver->getType($node->var);
        if (!$varType instanceof ObjectType) {
            return null;
        }
        if (!$varType->isInstanceOf('Doctrine\\DBAL\\Query\\QueryBuilder')->yes()) {
            return null;
        }
        if (!$this->isNames($node->name, ['select', 'addSelect', 'groupBy', 'addGroupBy'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if (\count($args) !== 1) {
            return null;
        }
        $currentArg = $args[0]->value;
        if (!$currentArg instanceof Array_) {
            return null;
        }
        $newArgs = [];
        foreach ($currentArg->items as $value) {
            if (!$value instanceof ArrayItem) {
                return null;
            }
            $newArgs[] = new Arg($value);
        }
        $node->args = $newArgs;
        return $node;
    }
}
