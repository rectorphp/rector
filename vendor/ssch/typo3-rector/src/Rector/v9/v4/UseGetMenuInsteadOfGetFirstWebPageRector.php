<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85971-DeprecatePageRepository-getFirstWebPage.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\UseGetMenuInsteadOfGetFirstWebPageRector\UseGetMenuInsteadOfGetFirstWebPageRectorTest
 */
final class UseGetMenuInsteadOfGetFirstWebPageRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->isName($node->name, 'getFirstWebPage')) {
            return null;
        }
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $rootLevelPagesVariable = new \PhpParser\Node\Expr\Variable('rootLevelPages');
        $this->addRootLevelPagesAssignment($rootLevelPagesVariable, $node);
        $resetRootLevelPagesNode = $this->nodeFactory->createFuncCall('reset', [$rootLevelPagesVariable]);
        $ifNode = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\Empty_($rootLevelPagesVariable)));
        $parentNode->expr = $resetRootLevelPagesNode;
        $ifNode->stmts[] = new \PhpParser\Node\Stmt\Expression($parentNode);
        $this->addNodeBeforeNode($ifNode, $node);
        try {
            $this->removeNode($node);
        } catch (\Rector\Core\Exception\ShouldNotHappenException $exception) {
            $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            $this->removeNode($parentNode);
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use method getMenu instead of getFirstWebPage', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$theFirstPage = $GLOBALS['TSFE']->sys_page->getFirstWebPage(0);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$rootLevelPages = $GLOBALS['TSFE']->sys_page->getMenu(0, 'uid', 'sorting', '', false);
if (!empty($rootLevelPages)) {
    $theFirstPage = reset($rootLevelPages);
}
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $node) : bool
    {
        if ($this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Page\\PageRepository'))) {
            return \false;
        }
        return !$this->typo3NodeResolver->isMethodCallOnPropertyOfGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER, 'sys_page');
    }
    private function addRootLevelPagesAssignment(\PhpParser\Node\Expr\Variable $rootLevelPagesVariable, \PhpParser\Node\Expr\MethodCall $node) : void
    {
        $rootLevelPagesNode = new \PhpParser\Node\Expr\Assign($rootLevelPagesVariable, $this->nodeFactory->createMethodCall($node->var, 'getMenu', [$node->args[0], 'uid', 'sorting', '', \false]));
        $this->addNodeBeforeNode($rootLevelPagesNode, $node);
    }
}
