<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v2;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.2/Deprecation-89756-BackendUtilityTYPO3_copyRightNotice.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v2\UseTypo3InformationForCopyRightNoticeRector\UseTypo3InformationForCopyRightNoticeRectorTest
 */
final class UseTypo3InformationForCopyRightNoticeRector extends AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Backend\\Utility\\BackendUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'TYPO3_copyRightNotice')) {
            return null;
        }
        $staticCall = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Information\\Typo3Information')]);
        return $this->nodeFactory->createMethodCall($staticCall, 'getCopyrightNotice');
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate the method BackendUtility::TYPO3_copyRightNotice() to use Typo3Information API', [new CodeSample(<<<'CODE_SAMPLE'
$copyright = BackendUtility::TYPO3_copyRightNotice();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$copyright = GeneralUtility::makeInstance(Typo3Information::class)->getCopyrightNotice();
CODE_SAMPLE
)]);
    }
}
