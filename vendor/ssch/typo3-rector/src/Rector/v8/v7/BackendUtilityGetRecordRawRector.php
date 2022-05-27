<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v7;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80317-DeprecateBackendUtilityGetRecordRaw.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\BackendUtilityGetRecordRawRector\BackendUtilityGetRecordRawRectorTest
 */
final class BackendUtilityGetRecordRawRector extends AbstractRector
{
    /**
     * @var string
     */
    private const QUERY_BUILDER = 'queryBuilder';
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Backend\\Utility\\BackendUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getRecordRaw')) {
            return null;
        }
        /** @var Arg[] $args */
        $args = $node->args;
        [$firstArgument, $secondArgument, $thirdArgument] = $args;
        $queryBuilderAssign = $this->createQueryBuilderCall($firstArgument);
        $queryBuilderRemoveRestrictions = $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(new Variable(self::QUERY_BUILDER), 'getRestrictions'), 'removeAll');
        $this->nodesToAddCollector->addNodesBeforeNode([new Nop(), $queryBuilderAssign, $queryBuilderRemoveRestrictions, new Nop()], $node);
        return $this->fetchQueryBuilderResults($firstArgument, $secondArgument, $thirdArgument);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate the method BackendUtility::editOnClick() to use UriBuilder API', [new CodeSample(<<<'CODE_SAMPLE'
$table = 'fe_users';
$where = 'uid > 5';
$fields = ['uid', 'pid'];
$record = BackendUtility::getRecordRaw($table, $where, $fields);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$table = 'fe_users';
$where = 'uid > 5';
$fields = ['uid', 'pid'];

$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
$queryBuilder->getRestrictions()->removeAll();

$record = $queryBuilder->select(GeneralUtility::trimExplode(',', $fields, true))
    ->from($table)
    ->where(QueryHelper::stripLogicalOperatorPrefix($where))
    ->execute()
    ->fetch();
CODE_SAMPLE
)]);
    }
    private function createQueryBuilderCall(Arg $firstArgument) : Assign
    {
        $queryBuilder = $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Database\\ConnectionPool')]), 'getQueryBuilderForTable', [$this->nodeFactory->createArg($firstArgument->value)]);
        return new Assign(new Variable(self::QUERY_BUILDER), $queryBuilder);
    }
    private function fetchQueryBuilderResults(Arg $table, Arg $where, Arg $fields) : MethodCall
    {
        $queryBuilder = new Variable(self::QUERY_BUILDER);
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($queryBuilder, 'select', [$this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'trimExplode', [new String_(','), $this->nodeFactory->createArg($fields->value), $this->nodeFactory->createTrue()])]), 'from', [$this->nodeFactory->createArg($table->value)]), 'where', [$this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Database\\Query\\QueryHelper', 'stripLogicalOperatorPrefix', [$this->nodeFactory->createArg($where->value)])]), 'execute'), 'fetch');
    }
}
