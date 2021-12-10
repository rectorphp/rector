<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-82426-Typo3-pagetreeNavigationComponentName.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\UseNewComponentIdForPageTreeRector\UseNewComponentIdForPageTreeRectorTest
 */
final class UseNewComponentIdForPageTreeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Utility\\ExtensionUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'registerModule')) {
            return null;
        }
        if (!isset($node->args[1], $node->args[5])) {
            return null;
        }
        if (!$this->valueResolver->isValue($node->args[1]->value, 'web')) {
            return null;
        }
        $moduleConfiguration = $node->args[5]->value;
        if (!$moduleConfiguration instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($moduleConfiguration->items as $item) {
            if (!$item instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $item->key) {
                continue;
            }
            if ('navigationComponentId' !== $this->valueResolver->getValue($item->key)) {
                continue;
            }
            if ('typo3-pagetree' !== $this->valueResolver->getValue($item->value)) {
                continue;
            }
            $item->value = new \PhpParser\Node\Scalar\String_('TYPO3/CMS/Backend/PageTree/PageTreeElement');
            $hasAstBeenChanged = \true;
        }
        return $hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use TYPO3/CMS/Backend/PageTree/PageTreeElement instead of typo3-pagetree', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('TYPO3\\CMS\\Extbase\\Utility\\ExtensionUtility' . '::registerModule(
      \'TYPO3.CMS.Workspaces\',
      \'web\',
      \'workspaces\',
      \'before:info\',
      [
          // An array holding the controller-action-combinations that are accessible
          \'Review\' => \'index,fullIndex,singleIndex\',
          \'Preview\' => \'index,newPage\'
      ],
      [
          \'access\' => \'user,group\',
          \'icon\' => \'EXT:workspaces/Resources/Public/Icons/module-workspaces.svg\',
          \'labels\' => \'LLL:EXT:workspaces/Resources/Private/Language/locallang_mod.xlf\',
          \'navigationComponentId\' => \'typo3-pagetree\'
      ]
  );', 'TYPO3\\CMS\\Extbase\\Utility\\ExtensionUtility' . '::registerModule(
      \'TYPO3.CMS.Workspaces\',
      \'web\',
      \'workspaces\',
      \'before:info\',
      [
          // An array holding the controller-action-combinations that are accessible
          \'Review\' => \'index,fullIndex,singleIndex\',
          \'Preview\' => \'index,newPage\'
      ],
      [
          \'access\' => \'user,group\',
          \'icon\' => \'EXT:workspaces/Resources/Public/Icons/module-workspaces.svg\',
          \'labels\' => \'LLL:EXT:workspaces/Resources/Private/Language/locallang_mod.xlf\',
          \'navigationComponentId\' => \'TYPO3/CMS/Backend/PageTree/PageTreeElement\'
      ]
  );')]);
    }
}
