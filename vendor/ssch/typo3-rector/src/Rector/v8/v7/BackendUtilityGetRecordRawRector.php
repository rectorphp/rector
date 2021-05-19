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
final class BackendUtilityGetRecordRawRector extends \Rector\Core\Rector\AbstractRector
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
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Utility\\BackendUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getRecordRaw')) {
            return null;
        }
        /** @var Arg[] $args */
        $args = $node->args;
        [$firstArgument, $secondArgument, $thirdArgument] = $args;
        $queryBuilderAssignment = $this->createQueryBuilderCall($firstArgument);
        $queryBuilderRemoveRestrictions = $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(new \PhpParser\Node\Expr\Variable(self::QUERY_BUILDER), 'getRestrictions'), 'removeAll');
        $this->addNodeBeforeNode(new \PhpParser\Node\Stmt\Nop(), $node);
        $this->addNodeBeforeNode($queryBuilderAssignment, $node);
        $this->addNodeBeforeNode($queryBuilderRemoveRestrictions, $node);
        $this->addNodeBeforeNode(new \PhpParser\Node\Stmt\Nop(), $node);
        return $this->fetchQueryBuilderResults($firstArgument, $secondArgument, $thirdArgument);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate the method BackendUtility::editOnClick() to use UriBuilder API', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    private function createQueryBuilderCall(\PhpParser\Node\Arg $firstArgument) : \PhpParser\Node\Expr\Assign
    {
        $queryBuilder = $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Database\\ConnectionPool')]), 'getQueryBuilderForTable', [$this->nodeFactory->createArg($firstArgument->value)]);
        return new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::QUERY_BUILDER), $queryBuilder);
    }
    private function fetchQueryBuilderResults(\PhpParser\Node\Arg $table, \PhpParser\Node\Arg $where, \PhpParser\Node\Arg $fields) : \PhpParser\Node\Expr\MethodCall
    {
        $queryBuilder = new \PhpParser\Node\Expr\Variable(self::QUERY_BUILDER);
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($queryBuilder, 'select', [$this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'trimExplode', [new \PhpParser\Node\Scalar\String_(','), $this->nodeFactory->createArg($fields->value), $this->nodeFactory->createTrue()])]), 'from', [$this->nodeFactory->createArg($table->value)]), 'where', [$this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Database\\Query\\QueryHelper', 'stripLogicalOperatorPrefix', [$this->nodeFactory->createArg($where->value)])]), 'execute'), 'fetch');
    }
}
