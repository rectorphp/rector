<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v7;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Echo_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80445-DeprecatePrintContentMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\RefactorPrintContentMethodsRector\RefactorPrintContentMethodsRectorTest
 */
final class RefactorPrintContentMethodsRector extends \Rector\Core\Rector\AbstractRector
{
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
        if (!$this->isName($node->name, 'printContent')) {
            return null;
        }
        if ($this->isPageLayoutControllerClass($node)) {
            $newNode = new \PhpParser\Node\Stmt\Echo_([$this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($node->var, 'getModuleTemplate'), 'renderContent')]);
        } else {
            $newNode = new \PhpParser\Node\Stmt\Echo_([$this->nodeFactory->createPropertyFetch($node->var, 'content')]);
        }
        try {
            $this->removeNode($node);
        } catch (\Rector\Core\Exception\ShouldNotHappenException $exception) {
            $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            $this->removeNode($parentNode);
        }
        $this->nodesToAddCollector->addNodeBeforeNode($newNode, $node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor printContent methods of classes TaskModuleController and PageLayoutController', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Taskcenter\Controller\TaskModuleController;
$pageLayoutController = GeneralUtility::makeInstance(PageLayoutController::class);
$pageLayoutController->printContent();

$taskLayoutController = GeneralUtility::makeInstance(TaskModuleController::class);
$taskLayoutController->printContent();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Core\Utility\GeneralUtility;use TYPO3\CMS\Taskcenter\Controller\TaskModuleController;
$pageLayoutController = GeneralUtility::makeInstance(PageLayoutController::class);
echo $pageLayoutController->getModuleTemplate()->renderContent();
$taskLayoutController = GeneralUtility::makeInstance(TaskModuleController::class);
echo $taskLayoutController->content;
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $node) : bool
    {
        if ($this->isPageLayoutControllerClass($node)) {
            return \false;
        }
        return !$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Taskcenter\\Controller\\TaskModuleController'));
    }
    private function isPageLayoutControllerClass(\PhpParser\Node\Expr\MethodCall $node) : bool
    {
        return $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Controller\\PageLayoutController'));
    }
}
