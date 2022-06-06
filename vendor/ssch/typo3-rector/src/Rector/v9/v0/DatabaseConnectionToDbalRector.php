<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\Helper\Database\Refactorings\DatabaseConnectionToDbalRefactoring;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Breaking-80929-TYPO3_DBMovedToExtension.html
 *
 * @see \Ssch\TYPO3Rector\Tests\Rector\Core\Database\DatabaseConnectionToDbalTest
 */
final class DatabaseConnectionToDbalRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    /**
     * @var DatabaseConnectionToDbalRefactoring[]
     * @readonly
     */
    private $databaseConnectionRefactorings;
    /**
     * @param DatabaseConnectionToDbalRefactoring[] $databaseConnectionRefactorings
     */
    public function __construct(Typo3NodeResolver $typo3NodeResolver, array $databaseConnectionRefactorings)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
        $this->databaseConnectionRefactorings = $databaseConnectionRefactorings;
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
                    $this->nodesToAddCollector->addNodeBeforeNode($newNode, $node);
                }
                $this->removeNode($node);
            }
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor legacy calls of DatabaseConnection to Dbal', [new CodeSample(<<<'CODE_SAMPLE'
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
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        return !$this->typo3NodeResolver->isAnyMethodCallOnGlobals($methodCall, Typo3NodeResolver::TYPO3_DB);
    }
}
