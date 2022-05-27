<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220527\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85666-TypoScriptFrontendController-initTemplate.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\RemoveInitTemplateMethodCallRector\RemoveInitTemplateMethodCallRectorTest
 */
final class RemoveInitTemplateMethodCallRector extends AbstractRector
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
        return [MethodCall::class, Expression::class];
    }
    /**
     * @param Expression|MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->typo3NodeResolver->isMethodCallOnGlobals($node, 'initTemplate', Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            $this->removeNode($node);
            return null;
        }
        if (!$node instanceof MethodCall) {
            return null;
        }
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'))) {
            return null;
        }
        if (!$this->isName($node->name, 'initTemplate')) {
            return null;
        }
        try {
            $this->removeNode($node);
        } catch (ShouldNotHappenException $exception) {
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            $this->removeNode($parentNode);
        }
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove method call initTemplate from TypoScriptFrontendController', [new CodeSample(<<<'CODE_SAMPLE'
$tsfe = GeneralUtility::makeInstance(TypoScriptFrontendController::class);
$tsfe->initTemplate();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$tsfe = GeneralUtility::makeInstance(TypoScriptFrontendController::class);
CODE_SAMPLE
)]);
    }
}
