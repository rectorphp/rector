<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Deprecation-88499-BackendUtilitygetViewDomain.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\BackendUtilityGetViewDomainToPageRouterRector\BackendUtilityGetViewDomainToPageRouterRectorTest
 */
final class BackendUtilityGetViewDomainToPageRouterRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Utility\\BackendUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getViewDomain')) {
            return null;
        }
        $siteNode = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable('site'), $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Site\\SiteFinder')]), 'getSiteByPageId', $node->args));
        $this->addNodeBeforeNode($siteNode, $node);
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(new \PhpParser\Node\Expr\Variable('site'), 'getRouter'), 'generateUri', [$node->args[0]]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor method call BackendUtility::getViewDomain() to PageRouter', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Backend\Utility\BackendUtility;
$domain1 = BackendUtility::getViewDomain(1);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId(1);
$domain1 = $site->getRouter()->generateUri(1);
CODE_SAMPLE
)]);
    }
}
