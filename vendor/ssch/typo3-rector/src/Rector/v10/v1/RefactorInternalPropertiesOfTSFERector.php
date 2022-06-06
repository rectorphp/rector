<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v1;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Coalesce;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\Empty_;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.1/Deprecation-89001-InternalPublicTSFEProperties.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v1\RefactorInternalPropertiesOfTSFERector\RefactorInternalPropertiesOfTSFERectorTest
 */
final class RefactorInternalPropertiesOfTSFERector extends AbstractRector
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
    public function __construct(Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor Internal public TSFE properties', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [PropertyFetch::class];
    }
    /**
     * @param PropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isNames($node->name, ['cHash_array', self::HASH, 'domainStartPage'])) {
            return null;
        }
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign && $parentNode->var === $node) {
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
    private function shouldSkip(PropertyFetch $propertyFetch) : bool
    {
        return !$this->typo3NodeResolver->isPropertyFetchOnAnyPropertyOfGlobals($propertyFetch, Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER);
    }
    private function initializeEmptyArray() : Node
    {
        return new Expression(new Assign(new Variable(self::RELEVANT_PARAMETERS_FOR_CACHING_FROM_PAGE_ARGUMENTS), $this->nodeFactory->createArray([])));
    }
    private function initializePageArguments() : Node
    {
        return new Expression(new Assign(new Variable(self::PAGE_ARGUMENTS), $this->createPageArguments()));
    }
    private function initializeQueryParams() : Node
    {
        return new Expression(new Assign(new Variable(self::QUERY_PARAMS), $this->nodeFactory->createMethodCall(new Variable(self::PAGE_ARGUMENTS), 'getDynamicArguments')));
    }
    private function getRelevantParametersFromCacheHashCalculator() : Node
    {
        $if = new If_(new BooleanAnd(new BooleanNot(new Empty_(new Variable(self::QUERY_PARAMS))), new Coalesce(new ArrayDimFetch($this->nodeFactory->createMethodCall(new Variable(self::PAGE_ARGUMENTS), 'getArguments'), new String_(self::HASH)), $this->nodeFactory->createFalse())));
        $if->stmts[] = new Expression(new Assign(new ArrayDimFetch(new Variable(self::QUERY_PARAMS), new String_('id')), $this->nodeFactory->createMethodCall(new Variable(self::PAGE_ARGUMENTS), 'getPageId')));
        $if->stmts[] = new Expression(new Assign(new Variable(self::RELEVANT_PARAMETERS_FOR_CACHING_FROM_PAGE_ARGUMENTS), $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')]), 'getRelevantParameters', [$this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\HttpUtility', 'buildQueryString', [new Variable(self::QUERY_PARAMS)])])));
        return $if;
    }
    private function refactorCacheHashArray(PropertyFetch $propertyFetch) : Node
    {
        $this->nodesToAddCollector->addNodesBeforeNode([$this->initializeEmptyArray(), $this->initializePageArguments(), $this->initializeQueryParams(), $this->getRelevantParametersFromCacheHashCalculator()], $propertyFetch);
        return new Variable(self::RELEVANT_PARAMETERS_FOR_CACHING_FROM_PAGE_ARGUMENTS);
    }
    private function refactorCacheHash() : Node
    {
        return new ArrayDimFetch($this->nodeFactory->createMethodCall($this->createPageArguments(), 'getArguments'), new String_(self::HASH));
    }
    private function createPageArguments() : MethodCall
    {
        return $this->nodeFactory->createMethodCall(new ArrayDimFetch(new Variable('GLOBALS'), new String_('REQUEST')), 'getAttribute', ['routing']);
    }
    private function refactorDomainStartPage() : Node
    {
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(new ArrayDimFetch(new Variable('GLOBALS'), new String_('REQUEST')), 'getAttribute', ['site']), 'getRootPageId');
    }
}
