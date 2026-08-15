<?php

declare (strict_types=1);
namespace Rector\Doctrine\Dbal31\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Dbal31\Rector\MethodCall\QueryBuilderExecuteToExecuteQueryOrExecuteStatementRector\QueryBuilderExecuteToExecuteQueryOrExecuteStatementRectorTest
 *
 * @changelog https://github.com/doctrine/dbal/pull/4578
 */
final class QueryBuilderExecuteToExecuteQueryOrExecuteStatementRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @var string
     */
    private const QUERY_BUILDER = 'Doctrine\DBAL\Query\QueryBuilder';
    /**
     * @var string[]
     */
    private const QUERY_METHODS = ['select', 'addSelect'];
    /**
     * @var string[]
     */
    private const STATEMENT_METHODS = ['insert', 'update', 'delete'];
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('doctrine/dbal', '>=3.1');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace QueryBuilder::execute() with executeQuery() or executeStatement() based on the query type', [new CodeSample(<<<'CODE_SAMPLE'
$queryBuilder
    ->select('u.id')
    ->from('user', 'u')
    ->execute();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$queryBuilder
    ->select('u.id')
    ->from('user', 'u')
    ->executeQuery();
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
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, 'execute')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType(self::QUERY_BUILDER))) {
            return null;
        }
        $newMethodName = $this->resolveNewMethodName($node->var);
        if ($newMethodName === null) {
            return null;
        }
        $node->name = new Identifier($newMethodName);
        return $node;
    }
    private function resolveNewMethodName(Expr $expr): ?string
    {
        while ($expr instanceof MethodCall) {
            $methodName = $this->getName($expr->name);
            if ($methodName !== null) {
                if (in_array($methodName, self::QUERY_METHODS, \true)) {
                    return 'executeQuery';
                }
                if (in_array($methodName, self::STATEMENT_METHODS, \true)) {
                    return 'executeStatement';
                }
            }
            $expr = $expr->var;
        }
        return null;
    }
}
