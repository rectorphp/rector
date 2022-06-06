<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v4;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220606\TYPO3\CMS\Frontend\Page\PageRepository;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85557-PageRepository-getRootLine.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\UseRootlineUtilityInsteadOfGetRootlineMethodRector\UseRootlineUtilityInsteadOfGetRootlineMethodRectorTest
 */
final class UseRootlineUtilityInsteadOfGetRootlineMethodRector extends AbstractRector
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
        if (!$this->isName($node->name, 'getRootLine')) {
            return null;
        }
        $mountPointParameter = $node->args[1] ?? $this->nodeFactory->createArg('');
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Utility\\RootlineUtility'), $node->args[0], $mountPointParameter]), 'get');
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use class RootlineUtility instead of method getRootLine', [new CodeSample(<<<'CODE_SAMPLE'
$rootline = $GLOBALS['TSFE']->sys_page->getRootLine(1);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
$rootline = GeneralUtility::makeInstance(RootlineUtility::class, 1)->get();
CODE_SAMPLE
)]);
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if ($this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new ObjectType('TYPO3\\CMS\\Frontend\\Page\\PageRepository'))) {
            return \false;
        }
        $methodCall->var->setAttribute(AttributeKey::PHP_DOC_INFO, $methodCall->getAttribute(AttributeKey::PHP_DOC_INFO));
        if ($this->isObjectType($methodCall->var, new ObjectType('TYPO3\\CMS\\Frontend\\Page\\PageRepository'))) {
            return \false;
        }
        return !$this->typo3NodeResolver->isMethodCallOnPropertyOfGlobals($methodCall, Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER, 'sys_page');
    }
}
