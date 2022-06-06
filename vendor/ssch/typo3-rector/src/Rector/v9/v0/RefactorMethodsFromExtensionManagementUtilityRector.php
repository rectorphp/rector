<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-82899-ExtensionManagementUtilityMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\RefactorMethodsFromExtensionManagementUtilityRector\RefactorMethodsFromExtensionManagementUtilityRectorTest
 */
final class RefactorMethodsFromExtensionManagementUtilityRector extends AbstractRector
{
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor deprecated methods from ExtensionManagementUtility.', [new CodeSample(<<<'CODE_SAMPLE'
ExtensionManagementUtility::removeCacheFiles();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class)->flushCachesInGroup('system');
CODE_SAMPLE
)]);
    }
    private function createNewMethodCallForSiteRelPath(StaticCall $staticCall) : StaticCall
    {
        $firstArgument = $staticCall->args[0];
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\PathUtility', 'stripPathSitePrefix', [$this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility', 'extPath', [$firstArgument])]);
    }
    private function createNewMethodCallForRemoveCacheFiles() : MethodCall
    {
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Cache\\CacheManager')]), 'flushCachesInGroup', [$this->nodeFactory->createArg('system')]);
    }
    private function removeSecondArgumentFromMethodIsLoaded(StaticCall $staticCall) : Node
    {
        $numberOfArguments = \count($staticCall->args);
        if ($numberOfArguments > 1) {
            unset($staticCall->args[1]);
        }
        return $staticCall;
    }
}
