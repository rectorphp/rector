<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-82899-ExtensionManagementUtilityMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\RefactorMethodsFromExtensionManagementUtilityRector\RefactorMethodsFromExtensionManagementUtilityRectorTest
 */
final class RefactorMethodsFromExtensionManagementUtilityRector extends \Rector\Core\Rector\AbstractRector
{
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
        $className = $this->getName($node->class);
        $methodName = $this->getName($node->name);
        if ('TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility' !== $className) {
            return null;
        }
        if (null === $methodName) {
            return null;
        }
        if ('isLoaded' === $methodName) {
            return $this->removeSecondArgumentFromMethodIsLoaded($node);
        }
        if ('siteRelPath' === $methodName) {
            return $this->createNewMethodCallForSiteRelPath($node);
        }
        if ('removeCacheFiles' === $methodName) {
            return $this->createNewMethodCallForRemoveCacheFiles();
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor deprecated methods from ExtensionManagementUtility.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
ExtensionManagementUtility::removeCacheFiles();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class)->flushCachesInGroup('system');
CODE_SAMPLE
)]);
    }
    private function createNewMethodCallForSiteRelPath(\PhpParser\Node\Expr\StaticCall $staticCall) : \PhpParser\Node\Expr\StaticCall
    {
        $firstArgument = $staticCall->args[0];
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\PathUtility', 'stripPathSitePrefix', [$this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility', 'extPath', [$firstArgument])]);
    }
    private function createNewMethodCallForRemoveCacheFiles() : \PhpParser\Node\Expr\MethodCall
    {
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Cache\\CacheManager')]), 'flushCachesInGroup', [$this->nodeFactory->createArg('system')]);
    }
    private function removeSecondArgumentFromMethodIsLoaded(\PhpParser\Node\Expr\StaticCall $staticCall) : \PhpParser\Node
    {
        $numberOfArguments = \count($staticCall->args);
        if ($numberOfArguments > 1) {
            unset($staticCall->args[1]);
        }
        return $staticCall;
    }
}
