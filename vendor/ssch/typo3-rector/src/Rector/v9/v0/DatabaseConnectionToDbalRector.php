<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Contract\Helper\Database\Refactorings\DatabaseConnectionToDbalRefactoring;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Breaking-80929-TYPO3_DBMovedToExtension.html
 *
 * @see \Ssch\TYPO3Rector\Tests\Rector\Core\Database\DatabaseConnectionToDbalTest
 */
final class DatabaseConnectionToDbalRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    /**
     * @var \Ssch\TYPO3Rector\Contract\Helper\Database\Refactorings\DatabaseConnectionToDbalRefactoring[]
     */
    private $databaseConnectionRefactorings;
    /**
     * @param DatabaseConnectionToDbalRefactoring[] $databaseConnectionRefactorings
     */
    public function __construct(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver $typo3NodeResolver, array $databaseConnectionRefactorings)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
        $this->databaseConnectionRefactorings = $databaseConnectionRefactorings;
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        $methodName = $this->getName($node->name);
        if (null === $methodName) {
            return null;
        }
        foreach ($this->databaseConnectionRefactorings as $databaseConnectionRefactoring) {
            if ($databaseConnectionRefactoring->canHandle($methodName)) {
                $nodes = $databaseConnectionRefactoring->refactor($node);
                foreach ($nodes as $newNode) {
                    $this->addNodeBeforeNode($newNode, $node);
                }
                $this->removeNode($node);
            }
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor legacy calls of DatabaseConnection to Dbal', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$GLOBALS['TYPO3_DB']->exec_INSERTquery(
            'pages',
            [
                'pid' => 0,
                'title' => 'Home',
            ]
        );
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$connectionPool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
        $databaseConnectionForPages = $connectionPool->getConnectionForTable('pages');
        $databaseConnectionForPages->insert(
            'pages',
            [
                'pid' => 0,
                'title' => 'Home',
            ]
        );
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $node) : bool
    {
        return !$this->typo3NodeResolver->isAnyMethodCallOnGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO3_DB);
    }
}
