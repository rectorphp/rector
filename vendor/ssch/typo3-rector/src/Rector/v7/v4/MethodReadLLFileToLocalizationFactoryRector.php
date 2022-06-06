<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v7\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.4/Deprecation-68122-GeneralUtilityReadLLfile.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v4\MethodReadLLFileToLocalizationFactoryRector\MethodReadLLFileToLocalizationFactoryRectorTest
 */
final class MethodReadLLFileToLocalizationFactoryRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'readLLfile')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Localization\\LocalizationFactory')]), 'getParsedData', $node->args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use LocalizationFactory->getParsedData instead of GeneralUtility::readLLfile', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
$locallangs = GeneralUtility::readLLfile('EXT:foo/locallang.xml', 'de');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$locallangs = GeneralUtility::makeInstance(LocalizationFactory::class)->getParsedData('EXT:foo/locallang.xml', 'de');
CODE_SAMPLE
)]);
    }
}
