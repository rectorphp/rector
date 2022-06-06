<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Int_;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80524-PageRepositorygetHashAndPageRepositorystoreHash.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\UseCachingFrameworkInsteadGetAndStoreHashRector\UseCachingFrameworkInsteadGetAndStoreHashRectorTest
 */
final class UseCachingFrameworkInsteadGetAndStoreHashRector extends AbstractRector
{
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
        return new RuleDefinition('Use the Caching Framework directly instead of methods PageRepository::getHash and PageRepository::storeHash', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
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
        $lifetime = isset($node->args[3]) ? new Int_($node->args[3]->value) : new LNumber(0);
        return $this->nodeFactory->createMethodCall($this->createCacheManager(), 'set', [$hash, $data, $this->nodeFactory->createArray([$this->nodeFactory->createConcat([new String_('ident_'), $ident])]), $lifetime]);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function shouldSkip($node) : bool
    {
        if ($this->typo3NodeResolver->isMethodCallOnSysPageOfTSFE($node)) {
            return \false;
        }
        return !$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Frontend\\Page\\PageRepository'));
    }
    private function createCacheManager() : MethodCall
    {
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Cache\\CacheManager')]), 'getCache', [new String_('cache_hash')]);
    }
}
