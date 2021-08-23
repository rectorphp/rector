<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Deprecation-86047-TSFEPropertiesMethodsAndChangeVisibility.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v5\RefactorPropertiesOfTypoScriptFrontendControllerRector\RefactorPropertiesOfTypoScriptFrontendControllerRectorTest
 */
final class RefactorPropertiesOfTypoScriptFrontendControllerRector extends \Rector\Core\Rector\AbstractRector
{
    /**
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
        return [\PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param PropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController')) && !$this->typo3NodeResolver->isPropertyFetchOnAnyPropertyOfGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            return null;
        }
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // Check if we have an assigment to the property, if so do not change it
        if ($parentNode instanceof \PhpParser\Node\Expr\Assign && $parentNode->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        if (!$this->isNames($node->name, ['ADMCMD_preview_BEUSER_uid', 'workspacePreview', 'loginAllowedInBranch'])) {
            return null;
        }
        if ($this->isName($node->name, 'loginAllowedInBranch')) {
            return $this->nodeFactory->createMethodCall($node->var, 'checkIfLoginAllowedInBranch');
        }
        $contextInstanceNode = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Context\\Context')]);
        if ($this->isName($node->name, 'ADMCMD_preview_BEUSER_uid')) {
            return $this->nodeFactory->createMethodCall($contextInstanceNode, 'getPropertyFromAspect', ['backend.user', 'id', 0]);
        }
        return $this->nodeFactory->createMethodCall($contextInstanceNode, 'getPropertyFromAspect', ['workspace', 'id', 0]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor some properties of TypoScriptFrontendController', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$previewBeUserUid = $GLOBALS['TSFE']->ADMCMD_preview_BEUSER_uid;
$workspacePreview = $GLOBALS['TSFE']->workspacePreview;
$loginAllowedInBranch = $GLOBALS['TSFE']->loginAllowedInBranch;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Context\Context;
$previewBeUserUid = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('backend.user', 'id', 0);
$workspacePreview = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('workspace', 'id', 0);
$loginAllowedInBranch = $GLOBALS['TSFE']->checkIfLoginAllowedInBranch();
CODE_SAMPLE
)]);
    }
}
