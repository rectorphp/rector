<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v4;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85878-EidUtilityAndVariousTSFEMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\RemoveMethodsFromEidUtilityAndTsfeRector\RemoveMethodsFromEidUtilityAndTsfeRectorTest
 */
final class RemoveMethodsFromEidUtilityAndTsfeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    public function __construct(Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class, MethodCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
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
        if ($this->isName($node->name, 'storeSessionData') && $node instanceof MethodCall) {
            return $this->delegateToFrontendUserProperty($node);
        }
        $this->removeMethodCall($node);
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove EidUtility and various TSFE methods', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Frontend\Utility\EidUtility;

EidUtility::initExtensionTCA('foo');
EidUtility::initFeUser();
EidUtility::initLanguage();
EidUtility::initTCA();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
-
CODE_SAMPLE
)]);
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    private function shouldSkip($call) : bool
    {
        if ($this->isEidUtilityMethodCall($call)) {
            return \false;
        }
        return !$this->isMethodCallOnTsfe($call);
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    private function isEidUtilityMethodCall($call) : bool
    {
        return $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($call, new ObjectType('TYPO3\\CMS\\Frontend\\Utility\\EidUtility'));
    }
    private function isMethodCallOnTsfe(Node $node) : bool
    {
        if ($this->typo3NodeResolver->isAnyMethodCallOnGlobals($node, Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            return \true;
        }
        return $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'));
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node $node
     */
    private function removeMethodCall($node) : void
    {
        try {
            parent::removeNode($node);
        } catch (ShouldNotHappenException $exception) {
        }
    }
    private function delegateToFrontendUserProperty(MethodCall $methodCall) : MethodCall
    {
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createPropertyFetch($methodCall->var, 'fe_user'), (string) $this->getName($methodCall->name));
    }
}
