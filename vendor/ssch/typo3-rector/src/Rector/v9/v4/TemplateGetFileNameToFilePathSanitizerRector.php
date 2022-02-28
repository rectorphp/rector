<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_ as ScalarString_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85445-TemplateService-getFileName.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\TemplateGetFileNameToFilePathSanitizerRector\TemplateGetFileNameToFilePathSanitizerRectorTest
 */
final class TemplateGetFileNameToFilePathSanitizerRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    public function __construct(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
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
        if (!$this->typo3NodeResolver->isMethodCallOnPropertyOfGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER, 'tmpl')) {
            return null;
        }
        if (!$this->isName($node->name, 'getFileName')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $filePath = new \PhpParser\Node\Expr\Cast\String_($node->args[0]->value);
        // First of all remove the node
        $this->removeNode($parentNode);
        $assignmentNode = $this->createSanitizeMethod($parentNode, $filePath);
        $assignmentNodeNull = $this->createNullAssignment($parentNode);
        $catches = [$this->createCatchBlockToIgnore($assignmentNodeNull), $this->createCatchBlockToLog([$assignmentNodeNull, $this->createIfLog()])];
        $tryCatchNode = new \PhpParser\Node\Stmt\TryCatch([$assignmentNode], $catches);
        $this->nodesToAddCollector->addNodeBeforeNode($tryCatchNode, $node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use FilePathSanitizer->sanitize() instead of TemplateService->getFileName()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$fileName = $GLOBALS['TSFE']->tmpl->getFileName('foo.text');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Resource\FilePathSanitizer;
use TYPO3\CMS\Core\Resource\Exception\InvalidFileNameException;
use TYPO3\CMS\Core\Resource\Exception\InvalidPathException;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\InvalidFileException;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
try {
    $fileName = GeneralUtility::makeInstance(FilePathSanitizer::class)->sanitize((string) 'foo.text');
} catch (InvalidFileNameException $e) {
    $fileName = null;
} catch (InvalidPathException|FileDoesNotExistException|InvalidFileException $e) {
    $fileName = null;
    if ($GLOBALS['TSFE']->tmpl->tt_track) {
        GeneralUtility::makeInstance(TimeTracker::class)->setTSlogMessage($e->getMessage(), 3);
    }
}
CODE_SAMPLE
)]);
    }
    private function createSanitizeMethod(\PhpParser\Node\Expr\Assign $parentNode, \PhpParser\Node\Expr\Cast\String_ $filePath) : \PhpParser\Node\Stmt\Expression
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($parentNode->var, $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Frontend\\Resource\\FilePathSanitizer')]), 'sanitize', [$filePath])));
    }
    private function createNullAssignment(\PhpParser\Node\Expr\Assign $parentNode) : \PhpParser\Node\Stmt\Expression
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($parentNode->var, $this->nodeFactory->createNull()));
    }
    private function createTimeTrackerLogMessage() : \PhpParser\Node\Stmt\Expression
    {
        $makeInstanceOfTimeTracker = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker')]);
        return new \PhpParser\Node\Stmt\Expression($this->nodeFactory->createMethodCall($makeInstanceOfTimeTracker, 'setTSlogMessage', [$this->nodeFactory->createMethodCall(new \PhpParser\Node\Expr\Variable('e'), 'getMessage'), $this->nodeFactory->createArg(3)]));
    }
    /**
     * @param Stmt[] $stmts
     */
    private function createCatchBlockToLog(array $stmts) : \PhpParser\Node\Stmt\Catch_
    {
        return new \PhpParser\Node\Stmt\Catch_([new \PhpParser\Node\Name('TYPO3\\CMS\\Core\\Resource\\Exception\\InvalidPathException'), new \PhpParser\Node\Name('TYPO3\\CMS\\Core\\Resource\\Exception\\FileDoesNotExistException'), new \PhpParser\Node\Name('TYPO3\\CMS\\Core\\Resource\\Exception\\InvalidFileException')], new \PhpParser\Node\Expr\Variable('e'), $stmts);
    }
    private function createCatchBlockToIgnore(\PhpParser\Node\Stmt\Expression $assignmentNodeNull) : \PhpParser\Node\Stmt\Catch_
    {
        return new \PhpParser\Node\Stmt\Catch_([new \PhpParser\Node\Name('TYPO3\\CMS\\Core\\Resource\\Exception\\InvalidFileNameException')], new \PhpParser\Node\Expr\Variable('e'), [$assignmentNodeNull]);
    }
    private function createIfLog() : \PhpParser\Node\Stmt\If_
    {
        $ifNode = new \PhpParser\Node\Stmt\If_($this->nodeFactory->createPropertyFetch($this->nodeFactory->createPropertyFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable('GLOBALS'), new \PhpParser\Node\Scalar\String_(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)), 'tmpl'), 'tt_track'));
        $ifNode->stmts[] = $this->createTimeTrackerLogMessage();
        return $ifNode;
    }
}
