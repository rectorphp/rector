<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Deprecation-86404-GLOBALSTYPO3_LOADED_EXT.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v5\UsePackageManagerActivePackagesRector\UsePackageManagerActivePackagesRectorTest
 */
final class UsePackageManagerActivePackagesRector extends \Rector\Core\Rector\AbstractRector
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
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ArrayDimFetch::class];
    }
    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->typo3NodeResolver->isTypo3Global($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO3_LOADED_EXT)) {
            return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Package\\PackageManager')]), 'getActivePackages');
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use PackageManager API instead of $GLOBALS[\'TYPO3_LOADED_EXT\']', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$extensionList = $GLOBALS['TYPO3_LOADED_EXT'];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$extensionList = GeneralUtility::makeInstance(PackageManager::class)->getActivePackages();
CODE_SAMPLE
)]);
    }
}
