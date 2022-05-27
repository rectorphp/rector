<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v4;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.4/Feature-94406-OverrideFileFolderTCAConfigurationWithTSconfig.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v4\MigrateFileFolderConfigurationRector\MigrateFileFolderConfigurationRectorTest
 */
final class MigrateFileFolderConfigurationRector extends AbstractTcaRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate file folder config', [new CodeSample(<<<'CODE_SAMPLE'
'aField' => [
   'config' => [
      'type' => 'select',
      'renderType' => 'selectSingle',
      'fileFolder' => 'EXT:my_ext/Resources/Public/Icons',
      'fileFolder_extList' => 'svg',
      'fileFolder_recursions' => 1,
   ]
]
CODE_SAMPLE
, <<<'CODE_SAMPLE'
'aField' => [
   'config' => [
      'type' => 'select',
      'renderType' => 'selectSingle',
      'fileFolderConfig' => [
         'folder' => 'EXT:styleguide/Resources/Public/Icons',
         'allowedExtensions' => 'svg',
         'depth' => 1,
      ]
   ]
]
CODE_SAMPLE
)]);
    }
    protected function refactorColumn(Expr $columnName, Expr $columnTca) : void
    {
        $configArray = $this->extractSubArrayByKey($columnTca, self::CONFIG);
        if (!$configArray instanceof Array_) {
            return;
        }
        if (!$this->hasKeyValuePair($configArray, self::TYPE, 'select') || !$this->hasKey($configArray, 'fileFolder')) {
            return;
        }
        $fileFolderConfig = new Array_();
        $fileFolder = $this->extractArrayItemByKey($configArray, 'fileFolder');
        if (null !== $fileFolder) {
            $fileFolderConfig->items[] = new ArrayItem($fileFolder->value, new String_('folder'));
            $this->removeNode($fileFolder);
            $this->hasAstBeenChanged = \true;
        }
        if ($this->hasKey($configArray, 'fileFolder_extList')) {
            $fileFolderExtList = $this->extractArrayItemByKey($configArray, 'fileFolder_extList');
            if (null !== $fileFolderExtList) {
                $fileFolderConfig->items[] = new ArrayItem($fileFolderExtList->value, new String_('allowedExtensions'));
                $this->removeNode($fileFolderExtList);
                $this->hasAstBeenChanged = \true;
            }
        }
        if ($this->hasKey($configArray, 'fileFolder_recursions')) {
            $fileFolderRecursions = $this->extractArrayItemByKey($configArray, 'fileFolder_recursions');
            if (null !== $fileFolderRecursions) {
                $fileFolderConfig->items[] = new ArrayItem($fileFolderRecursions->value, new String_('depth'));
                $this->removeNode($fileFolderRecursions);
                $this->hasAstBeenChanged = \true;
            }
        }
        $configArray->items[] = new ArrayItem($fileFolderConfig, new String_('fileFolderConfig'));
    }
}
