<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v1;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.1/Feature-88907-AlwaysEnableFilterInSelectMultipleSideBySideFields.html?highlight=enablemultiselectfiltertextfield
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v1\RemoveEnableMultiSelectFilterTextfieldRector\RemoveEnableMultiSelectFilterTextfieldRectorTest
 */
final class RemoveEnableMultiSelectFilterTextfieldRector extends \Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector
{
    use TcaHelperTrait;
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove "enableMultiSelectFilterTextfield" => true as its default', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
'foo' => [
   'label' => 'foo',
   'config' => [
      'type' => 'select',
      'renderType' => 'selectMultipleSideBySide',
      'enableMultiSelectFilterTextfield' => true,
   ]
],
CODE_SAMPLE
, <<<'CODE_SAMPLE'
'foo' => [
   'label' => 'foo',
   'config' => [
      'type' => 'select',
      'renderType' => 'selectMultipleSideBySide',
   ]
],
CODE_SAMPLE
)]);
    }
    protected function refactorColumn(\PhpParser\Node\Expr $columnName, \PhpParser\Node\Expr $columnTca) : void
    {
        $configArray = $this->extractSubArrayByKey($columnTca, self::CONFIG);
        if (!$configArray instanceof \PhpParser\Node\Expr\Array_) {
            return;
        }
        if (!$this->configIsOfRenderType($configArray, 'selectMultipleSideBySide')) {
            return;
        }
        $toRemoveArrayItem = $this->extractArrayItemByKey($configArray, 'enableMultiSelectFilterTextfield');
        if (!$toRemoveArrayItem instanceof \PhpParser\Node\Expr\ArrayItem || null === $toRemoveArrayItem->value) {
            return;
        }
        $nodeValue = $this->valueResolver->getValue($toRemoveArrayItem->value);
        if (\true === $nodeValue) {
            $this->removeNode($toRemoveArrayItem);
            $this->hasAstBeenChanged = \true;
        }
    }
}
