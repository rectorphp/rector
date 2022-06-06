<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v1;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.1/Deprecation-89001-InternalPublicTSFEProperties.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v1\RefactorInternalPropertiesOfTSFERector\RefactorInternalPropertiesOfTSFERectorTest
 */
final class RefactorInternalPropertiesOfTSFERector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const HASH = 'cHash';
    /**
     * @var string
     */
    private const RELEVANT_PARAMETERS_FOR_CACHING_FROM_PAGE_ARGUMENTS = 'relevantParametersForCachingFromPageArguments';
    /**
     * @var string
     */
    private const PAGE_ARGUMENTS = 'pageArguments';
    /**
     * @var string
     */
    private const QUERY_PARAMS = 'queryParams';
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    public function __construct(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor Internal public TSFE properties', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$domainStartPage = $GLOBALS['TSFE']->domainStartPage;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$cHash = $GLOBALS['REQUEST']->getAttribute('routing')->getArguments()['cHash'];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param PropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isNames($node->name, ['cHash_array', self::HASH, 'domainStartPage'])) {
            return null;
        }
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Expr\Assign && $parentNode->var === $node) {
            return null;
        }
        if ($this->isName($node->name, 'cHash_array')) {
            return $this->refactorCacheHashArray($node);
        }
        if ($this->isName($node->name, self::HASH)) {
            return $this->refactorCacheHash();
        }
        return $this->refactorDomainStartPage();
    }
    private function shouldSkip(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : bool
    {
        return !$this->typo3NodeResolver->isPropertyFetchOnAnyPropertyOfGlobals($propertyFetch, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER);
    }
    private function initializeEmptyArray() : \PhpParser\Node
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::RELEVANT_PARAMETERS_FOR_CACHING_FROM_PAGE_ARGUMENTS), $this->nodeFactory->createArray([])));
    }
    private function initializePageArguments() : \PhpParser\Node
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::PAGE_ARGUMENTS), $this->createPageArguments()));
    }
    private function initializeQueryParams() : \PhpParser\Node
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::QUERY_PARAMS), $this->nodeFactory->createMethodCall(new \PhpParser\Node\Expr\Variable(self::PAGE_ARGUMENTS), 'getDynamicArguments')));
    }
    private function getRelevantParametersFromCacheHashCalculator() : \PhpParser\Node
    {
        $if = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BinaryOp\BooleanAnd(new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\Empty_(new \PhpParser\Node\Expr\Variable(self::QUERY_PARAMS))), new \PhpParser\Node\Expr\BinaryOp\Coalesce(new \PhpParser\Node\Expr\ArrayDimFetch($this->nodeFactory->createMethodCall(new \PhpParser\Node\Expr\Variable(self::PAGE_ARGUMENTS), 'getArguments'), new \PhpParser\Node\Scalar\String_(self::HASH)), $this->nodeFactory->createFalse())));
        $if->stmts[] = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable(self::QUERY_PARAMS), new \PhpParser\Node\Scalar\String_('id')), $this->nodeFactory->createMethodCall(new \PhpParser\Node\Expr\Variable(self::PAGE_ARGUMENTS), 'getPageId')));
        $if->stmts[] = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::RELEVANT_PARAMETERS_FOR_CACHING_FROM_PAGE_ARGUMENTS), $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')]), 'getRelevantParameters', [$this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\HttpUtility', 'buildQueryString', [new \PhpParser\Node\Expr\Variable(self::QUERY_PARAMS)])])));
        return $if;
    }
    private function refactorCacheHashArray(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : \PhpParser\Node
    {
        $this->nodesToAddCollector->addNodesBeforeNode([$this->initializeEmptyArray(), $this->initializePageArguments(), $this->initializeQueryParams(), $this->getRelevantParametersFromCacheHashCalculator()], $propertyFetch);
        return new \PhpParser\Node\Expr\Variable(self::RELEVANT_PARAMETERS_FOR_CACHING_FROM_PAGE_ARGUMENTS);
    }
    private function refactorCacheHash() : \PhpParser\Node
    {
        return new \PhpParser\Node\Expr\ArrayDimFetch($this->nodeFactory->createMethodCall($this->createPageArguments(), 'getArguments'), new \PhpParser\Node\Scalar\String_(self::HASH));
    }
    private function createPageArguments() : \PhpParser\Node\Expr\MethodCall
    {
        return $this->nodeFactory->createMethodCall(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable('GLOBALS'), new \PhpParser\Node\Scalar\String_('REQUEST')), 'getAttribute', ['routing']);
    }
    private function refactorDomainStartPage() : \PhpParser\Node
    {
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable('GLOBALS'), new \PhpParser\Node\Scalar\String_('REQUEST')), 'getAttribute', ['site']), 'getRootPageId');
    }
}
