<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85701-MethodsInModuleTemplate.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\UseAddJsFileInsteadOfLoadJavascriptLibRector\UseAddJsFileInsteadOfLoadJavascriptLibRectorTest
 */
final class UseAddJsFileInsteadOfLoadJavascriptLibRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Template\\ModuleTemplate'))) {
            return null;
        }
        if (!$this->isName($node->name, 'loadJavascriptLib')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Page\\PageRenderer')]), 'addJsFile', $node->args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use method addJsFile of class PageRenderer instead of method loadJavascriptLib of class ModuleTemplate', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
$moduleTemplate->loadJavascriptLib('sysext/backend/Resources/Public/JavaScript/md5.js');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
GeneralUtility::makeInstance(PageRenderer::class)->addJsFile('sysext/backend/Resources/Public/JavaScript/md5.js');
CODE_SAMPLE
)]);
    }
}
