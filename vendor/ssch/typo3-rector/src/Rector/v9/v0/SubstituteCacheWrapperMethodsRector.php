<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Concat;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-83116-CachingFrameworkWrapperMethodsInBackendUtility.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\SubstituteCacheWrapperMethodsRector\SubstituteCacheWrapperMethodsRectorTest
 */
final class SubstituteCacheWrapperMethodsRector extends AbstractRector
{
    /**
     * @var string
     */
    private const CACHE_ENTRY = 'cacheEntry';
    /**
     * @var string
     */
    private const CACHE_MANAGER = 'cacheManager';
    /**
     * @var string
     */
    private const HASH_CONTENT = 'hashContent';
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
        if (!$this->isNames($node->name, ['getHash', 'storeHash'])) {
            return null;
        }
        if ($this->isName($node->name, 'getHash')) {
            $this->getCacheMethod($node);
        } elseif ($this->isName($node->name, 'storeHash')) {
            $this->setCacheMethod($node);
        }
        // At the end, remove the old method call node
        try {
            $this->removeNode($node);
        } catch (ShouldNotHappenException $exception) {
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            $this->removeNode($parentNode);
        }
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Caching framework wrapper methods in BackendUtility', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Backend\Utility\BackendUtility;
$hash = 'foo';
$content = BackendUtility::getHash($hash);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$hash = 'foo';
$cacheManager = GeneralUtility::makeInstance(CacheManager::class);
$cacheEntry = $cacheManager->getCache('cache_hash')->get($hash);
$hashContent = null;
if ($cacheEntry) {
    $hashContent = $cacheEntry;
}
$content = $hashContent;
CODE_SAMPLE
)]);
    }
    private function createCacheManager() : StaticCall
    {
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Cache\\CacheManager')]);
    }
    private function getCacheMethod(StaticCall $staticCall) : void
    {
        $this->addCacheManagerNode($staticCall);
        $cacheEntryAssign = new Assign(new Variable(self::CACHE_ENTRY), $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(self::CACHE_MANAGER, 'getCache', ['cache_hash']), 'get', [$staticCall->args[0]]));
        $this->nodesToAddCollector->addNodeAfterNode($cacheEntryAssign, $staticCall);
        $hashContentAssign = new Assign(new Variable(self::HASH_CONTENT), $this->nodeFactory->createNull());
        $this->nodesToAddCollector->addNodeAfterNode($hashContentAssign, $staticCall);
        $if = new If_(new Variable(self::CACHE_ENTRY));
        $if->stmts[] = new Expression(new Assign(new Variable(self::HASH_CONTENT), new Variable(self::CACHE_ENTRY)));
        $this->nodesToAddCollector->addNodeAfterNode($if, $staticCall);
        $this->nodesToAddCollector->addNodeAfterNode(new Assign(new Variable('content'), new Variable(self::HASH_CONTENT)), $staticCall);
    }
    private function addCacheManagerNode(StaticCall $staticCall) : void
    {
        $cacheManagerAssign = new Assign(new Variable(self::CACHE_MANAGER), $this->createCacheManager());
        $this->nodesToAddCollector->addNodeAfterNode($cacheManagerAssign, $staticCall);
    }
    private function setCacheMethod(StaticCall $staticCall) : void
    {
        $this->addCacheManagerNode($staticCall);
        $arguments = [$staticCall->args[0], $staticCall->args[1], new Array_([new ArrayItem(new Concat(new String_('ident_'), $staticCall->args[2]->value))]), $this->nodeFactory->createArg(0)];
        $cacheEntryNode = $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(self::CACHE_MANAGER, 'getCache', ['cache_hash']), 'set', $arguments);
        $this->nodesToAddCollector->addNodeAfterNode($cacheEntryNode, $staticCall);
    }
}
