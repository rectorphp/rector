<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v2;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.2/Feature-84153-IntroduceAGenericEnvironmentClass.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v2\RenameMethodCallToEnvironmentMethodCallRector\RenameMethodCallToEnvironmentMethodCallRectorTest
 */
final class RenameMethodCallToEnvironmentMethodCallRector extends AbstractRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns method call names to new ones from new Environment API.', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
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
