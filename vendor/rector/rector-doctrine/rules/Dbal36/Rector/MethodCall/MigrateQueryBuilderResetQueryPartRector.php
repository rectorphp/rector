<?php

declare (strict_types=1);
namespace Rector\Doctrine\Dbal36\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/dbal/blob/4.2.x/UPGRADE.md#deprecated-getting-query-parts-from-querybuilder
 * @see \Rector\Doctrine\Tests\Dbal36\Rector\MethodCall\MigrateQueryBuilderResetQueryPartRector\MigrateQueryBuilderResetQueryPartRectorTest
 */
final class MigrateQueryBuilderResetQueryPartRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const PART_TO_METHOD_MAP = ['where' => 'resetWhere', 'groupBy' => 'resetGroupBy', 'having' => 'resetHaving', 'orderBy' => 'resetOrderBy'];
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change QueryBuilder::resetQueryPart() to $queryBuilder->reset*()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeRepository
{
    public function resetQueryPart(\Doctrine\DBAL\Query\QueryBuilder $queryBuilder)
    {
        $queryBuilder->resetQueryPart('distinct');
        $queryBuilder->resetQueryPart('where');
        $queryBuilder->resetQueryPart('groupBy');
        $queryBuilder->resetQueryPart('having');
        $queryBuilder->resetQueryPart('orderBy');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeRepository
{
    public function resetQueryPart(\Doctrine\DBAL\Query\QueryBuilder $queryBuilder)
    {
        $queryBuilder->distinct(false);
        $queryBuilder->resetWhere();
        $queryBuilder->resetGroupBy();
        $queryBuilder->resetHaving();
        $queryBuilder->resetOrderBy();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isObjectType($node->var, new ObjectType('Doctrine\DBAL\Query\QueryBuilder'))) {
            return null;
        }
        if (!$this->isName($node->name, 'resetQueryPart')) {
            return null;
        }
        $args = $node->args;
        if (!isset($args[0])) {
            return null;
        }
        /** @var Arg $firstArgument */
        $firstArgument = $args[0];
        $argValue = $firstArgument->value;
        if (!$argValue instanceof String_) {
            return null;
        }
        $queryPartName = $argValue->value;
        if ($queryPartName === 'distinct') {
            $node->name = new Identifier('distinct');
            $node->args = [$this->nodeFactory->createArg($this->nodeFactory->createFalse())];
            return $node;
        }
        if (isset(self::PART_TO_METHOD_MAP[$queryPartName])) {
            $newMethodName = self::PART_TO_METHOD_MAP[$queryPartName];
            $node->name = new Identifier($newMethodName);
            $node->args = [];
            return $node;
        }
        return null;
    }
}
