<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\typo3;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97531-ContextRelatedMethodsWithinTSFE.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v12\v0\typo3\ReplaceTSFEWithContextMethodsRector\ReplaceTSFEWithContextMethodsRectorTest
 */
final class ReplaceTSFEWithContextMethodsRector extends AbstractRector
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
    public function __construct(Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isNames($node->name, ['initUserGroups', self::IS_USER_OR_GROUP_SET, 'isBackendUserLoggedIn', 'doWorkspacePreview', 'whichWorkspace'])) {
            return null;
        }
        $contextCall = $this->nodeFactory->createMethodCall($node->var, 'getContext');
        if ($this->isName($node->name, 'isBackendUserLoggedIn')) {
            $arguments = [new String_('backend.user'), new String_('isLoggedIn'), $this->nodeFactory->createFalse()];
            return $this->createPropertyFromAspectCall($contextCall, $arguments);
        }
        if ($this->isName($node->name, 'doWorkspacePreview')) {
            $arguments = [new String_('workspace'), new String_('isOffline'), $this->nodeFactory->createFalse()];
            return $this->createPropertyFromAspectCall($contextCall, $arguments);
        }
        if ($this->isName($node->name, 'whichWorkspace')) {
            $arguments = [new String_('workspace'), new String_('id'), $this->nodeFactory->createArg(0)];
            return $this->createPropertyFromAspectCall($contextCall, $arguments);
        }
        if ($this->isName($node->name, self::IS_USER_OR_GROUP_SET)) {
            return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($contextCall, 'getAspect', [new String_('frontend.user')]), self::IS_USER_OR_GROUP_SET);
        }
        $userAspectCall = $this->nodeFactory->createMethodCall($this->nodeFactory->createPropertyFetch($node->var, 'fe_user'), 'createUserAspect');
        return $this->nodeFactory->createMethodCall($contextCall, 'setAspect', [new String_('frontend.user'), $userAspectCall]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace TSFE with Context methods', [new CodeSample(<<<'CODE_SAMPLE'
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
    private function createPropertyFromAspectCall(MethodCall $contextCall, array $arguments) : MethodCall
    {
        return $this->nodeFactory->createMethodCall($contextCall, 'getPropertyFromAspect', $arguments);
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if ($this->typo3NodeResolver->isAnyMethodCallOnGlobals($methodCall, Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            return \false;
        }
        return !$this->isObjectType($methodCall->var, new ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'));
    }
}
