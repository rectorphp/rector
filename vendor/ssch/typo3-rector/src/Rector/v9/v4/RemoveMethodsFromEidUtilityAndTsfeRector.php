<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85878-EidUtilityAndVariousTSFEMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\RemoveMethodsFromEidUtilityAndTsfeRector\RemoveMethodsFromEidUtilityAndTsfeRectorTest
 */
final class RemoveMethodsFromEidUtilityAndTsfeRector extends \Rector\Core\Rector\AbstractRector
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
        return [\PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if ($this->isEidUtilityMethodCall($node)) {
            $this->removeMethodCall($node);
            return null;
        }
        if (!$this->isNames($node->name, ['initFEuser', 'storeSessionData', 'previewInfo', 'hook_eofe', 'addTempContentHttpHeaders', 'sendCacheHeaders'])) {
            return null;
        }
        if ($this->isName($node->name, 'storeSessionData') && $node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->delegateToFrontendUserProperty($node);
        }
        $this->removeMethodCall($node);
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove EidUtility and various TSFE methods', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Frontend\Utility\EidUtility;
EidUtility::initExtensionTCA('foo');
EidUtility::initFeUser();
EidUtility::initLanguage();
EidUtility::initTCA();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
''
CODE_SAMPLE
)]);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function shouldSkip($node) : bool
    {
        if ($this->isEidUtilityMethodCall($node)) {
            return \false;
        }
        return !$this->isMethodCallOnTsfe($node);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function isEidUtilityMethodCall($node) : bool
    {
        return $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Utility\\EidUtility'));
    }
    private function isMethodCallOnTsfe(\PhpParser\Node $node) : bool
    {
        if ($this->typo3NodeResolver->isAnyMethodCallOnGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            return \true;
        }
        return $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'));
    }
    /**
     * @param \PhpParser\Node|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function removeMethodCall($node) : void
    {
        try {
            parent::removeNode($node);
        } catch (\Rector\Core\Exception\ShouldNotHappenException $exception) {
        }
    }
    private function delegateToFrontendUserProperty(\PhpParser\Node\Expr\MethodCall $node) : \PhpParser\Node\Expr\MethodCall
    {
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createPropertyFetch($node->var, 'fe_user'), (string) $this->getName($node->name));
    }
}
