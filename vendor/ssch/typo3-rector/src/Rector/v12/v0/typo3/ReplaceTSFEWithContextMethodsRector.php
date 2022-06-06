<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v12\v0\typo3;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97531-ContextRelatedMethodsWithinTSFE.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v12\v0\typo3\ReplaceTSFEWithContextMethodsRector\ReplaceTSFEWithContextMethodsRectorTest
 */
final class ReplaceTSFEWithContextMethodsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const IS_USER_OR_GROUP_SET = 'isUserOrGroupSet';
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isNames($node->name, ['initUserGroups', self::IS_USER_OR_GROUP_SET, 'isBackendUserLoggedIn', 'doWorkspacePreview', 'whichWorkspace'])) {
            return null;
        }
        $contextCall = $this->nodeFactory->createMethodCall($node->var, 'getContext');
        if ($this->isName($node->name, 'isBackendUserLoggedIn')) {
            $arguments = [new \PhpParser\Node\Scalar\String_('backend.user'), new \PhpParser\Node\Scalar\String_('isLoggedIn'), $this->nodeFactory->createFalse()];
            return $this->createPropertyFromAspectCall($contextCall, $arguments);
        }
        if ($this->isName($node->name, 'doWorkspacePreview')) {
            $arguments = [new \PhpParser\Node\Scalar\String_('workspace'), new \PhpParser\Node\Scalar\String_('isOffline'), $this->nodeFactory->createFalse()];
            return $this->createPropertyFromAspectCall($contextCall, $arguments);
        }
        if ($this->isName($node->name, 'whichWorkspace')) {
            $arguments = [new \PhpParser\Node\Scalar\String_('workspace'), new \PhpParser\Node\Scalar\String_('id'), $this->nodeFactory->createArg(0)];
            return $this->createPropertyFromAspectCall($contextCall, $arguments);
        }
        if ($this->isName($node->name, self::IS_USER_OR_GROUP_SET)) {
            return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($contextCall, 'getAspect', [new \PhpParser\Node\Scalar\String_('frontend.user')]), self::IS_USER_OR_GROUP_SET);
        }
        $userAspectCall = $this->nodeFactory->createMethodCall($this->nodeFactory->createPropertyFetch($node->var, 'fe_user'), 'createUserAspect');
        return $this->nodeFactory->createMethodCall($contextCall, 'setAspect', [new \PhpParser\Node\Scalar\String_('frontend.user'), $userAspectCall]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace TSFE with Context methods', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$GLOBALS['TSFE']->initUserGroups();

$GLOBALS['TSFE']->isUserOrGroupSet();

$GLOBALS['TSFE']->isBackendUserLoggedIn();

$GLOBALS['TSFE']->doWorkspacePreview();

$GLOBALS['TSFE']->whichWorkspace();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$GLOBALS['TSFE']->getContext()->setAspect('frontend.user', $GLOBALS['TSFE']->fe_user->createUserAspect());

$GLOBALS['TSFE']->getContext()->getAspect('frontend.user')->isUserOrGroupSet();

$GLOBALS['TSFE']->getContext()->getPropertyFromAspect('backend.user', 'isLoggedIn', false);

$GLOBALS['TSFE']->getContext()->getPropertyFromAspect('workspace', 'isOffline', false);

$GLOBALS['TSFE']->getContext()->getPropertyFromAspect('workspace', 'id', 0);
CODE_SAMPLE
)]);
    }
    /**
     * @param mixed[] $arguments
     */
    private function createPropertyFromAspectCall(\PhpParser\Node\Expr\MethodCall $contextCall, array $arguments) : \PhpParser\Node\Expr\MethodCall
    {
        return $this->nodeFactory->createMethodCall($contextCall, 'getPropertyFromAspect', $arguments);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if ($this->typo3NodeResolver->isAnyMethodCallOnGlobals($methodCall, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            return \false;
        }
        return !$this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'));
    }
}
