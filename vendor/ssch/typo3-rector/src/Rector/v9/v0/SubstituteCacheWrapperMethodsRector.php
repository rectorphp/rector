<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-83116-CachingFrameworkWrapperMethodsInBackendUtility.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\SubstituteCacheWrapperMethodsRector\SubstituteCacheWrapperMethodsRectorTest
 */
final class SubstituteCacheWrapperMethodsRector extends \Rector\Core\Rector\AbstractRector
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
        } catch (\Rector\Core\Exception\ShouldNotHappenException $exception) {
            $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            $this->removeNode($parentNode);
        }
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Caching framework wrapper methods in BackendUtility', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    private function createCacheManager() : \PhpParser\Node\Expr\StaticCall
    {
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Cache\\CacheManager')]);
    }
    private function getCacheMethod(\PhpParser\Node\Expr\StaticCall $node) : void
    {
        $this->addCacheManagerNode($node);
        $cacheEntryNode = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::CACHE_ENTRY), $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(self::CACHE_MANAGER, 'getCache', ['cache_hash']), 'get', [$node->args[0]]));
        $this->nodesToAddCollector->addNodeAfterNode($cacheEntryNode, $node);
        $hashContentNode = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::HASH_CONTENT), $this->nodeFactory->createNull());
        $this->nodesToAddCollector->addNodeAfterNode($hashContentNode, $node);
        $ifNode = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\Variable(self::CACHE_ENTRY));
        $ifNode->stmts[] = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::HASH_CONTENT), new \PhpParser\Node\Expr\Variable(self::CACHE_ENTRY)));
        $this->nodesToAddCollector->addNodeAfterNode($ifNode, $node);
        $this->nodesToAddCollector->addNodeAfterNode(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable('content'), new \PhpParser\Node\Expr\Variable(self::HASH_CONTENT)), $node);
    }
    private function addCacheManagerNode(\PhpParser\Node\Expr\StaticCall $node) : void
    {
        $cacheManagerNode = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::CACHE_MANAGER), $this->createCacheManager());
        $this->nodesToAddCollector->addNodeAfterNode($cacheManagerNode, $node);
    }
    private function setCacheMethod(\PhpParser\Node\Expr\StaticCall $node) : void
    {
        $this->addCacheManagerNode($node);
        $arguments = [$node->args[0], $node->args[1], new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Scalar\String_('ident_'), $node->args[2]->value))]), $this->nodeFactory->createArg(0)];
        $cacheEntryNode = $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(self::CACHE_MANAGER, 'getCache', ['cache_hash']), 'set', $arguments);
        $this->nodesToAddCollector->addNodeAfterNode($cacheEntryNode, $node);
    }
}
