<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v2;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.2/Feature-84153-IntroduceAGenericEnvironmentClass.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v2\RenameMethodCallToEnvironmentMethodCallRector\RenameMethodCallToEnvironmentMethodCallRectorTest
 */
final class RenameMethodCallToEnvironmentMethodCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns method call names to new ones from new Environment API.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
Bootstrap::usesComposerClassLoading();
GeneralUtility::getApplicationContext();
EnvironmentService::isEnvironmentInCliMode();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Environment::isComposerMode();
Environment::getContext();
Environment::isCli();
CODE_SAMPLE
)]);
    }
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
        if ('TYPO3\\CMS\\Core\\Core\\Bootstrap' === $className && 'usesComposerClassLoading' === $methodName) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'isComposerMode');
        }
        if ('TYPO3\\CMS\\Core\\Utility\\GeneralUtility' === $className && 'getApplicationContext' === $methodName) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'getContext');
        }
        if ('TYPO3\\CMS\\Extbase\\Service\\EnvironmentService' === $className && 'isEnvironmentInCliMode' === $methodName) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'isCli');
        }
        return null;
    }
}
