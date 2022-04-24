<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v7;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80524-PageRepositorygetHashAndPageRepositorystoreHash.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\UseCachingFrameworkInsteadGetAndStoreHashRector\UseCachingFrameworkInsteadGetAndStoreHashRectorTest
 */
final class UseCachingFrameworkInsteadGetAndStoreHashRector extends \Rector\Core\Rector\AbstractRector
{
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use the Caching Framework directly instead of methods PageRepository::getHash and PageRepository::storeHash', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$GLOBALS['TSFE']->sys_page->storeHash('hash', ['foo', 'bar', 'baz'], 'ident');
$hashContent2 = $GLOBALS['TSFE']->sys_page->getHash('hash');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_hash')->set('hash', ['foo', 'bar', 'baz'], ['ident_' . 'ident'], 0);
$hashContent = GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_hash')->get('hash');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isNames($node->name, ['storeHash', 'getHash'])) {
            return null;
        }
        if ($this->isName($node->name, 'getHash')) {
            if (!isset($node->args[0])) {
                return null;
            }
            return $this->nodeFactory->createMethodCall($this->createCacheManager(), 'get', [$node->args[0]->value]);
        }
        if (!isset($node->args[0], $node->args[1], $node->args[2])) {
            return null;
        }
        $hash = $node->args[0]->value;
        $data = $node->args[1]->value;
        $ident = $node->args[2]->value;
        $lifetime = isset($node->args[3]) ? new \PhpParser\Node\Expr\Cast\Int_($node->args[3]->value) : new \PhpParser\Node\Scalar\LNumber(0);
        return $this->nodeFactory->createMethodCall($this->createCacheManager(), 'set', [$hash, $data, $this->nodeFactory->createArray([$this->nodeFactory->createConcat([new \PhpParser\Node\Scalar\String_('ident_'), $ident])]), $lifetime]);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function shouldSkip($node) : bool
    {
        if ($this->typo3NodeResolver->isMethodCallOnSysPageOfTSFE($node)) {
            return \false;
        }
        return !$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Page\\PageRepository'));
    }
    private function createCacheManager() : \PhpParser\Node\Expr\MethodCall
    {
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Cache\\CacheManager')]), 'getCache', [new \PhpParser\Node\Scalar\String_('cache_hash')]);
    }
}
