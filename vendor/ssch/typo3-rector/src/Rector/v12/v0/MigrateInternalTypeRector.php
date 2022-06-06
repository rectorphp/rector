<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v12\v0;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-96983-TCAInternal_type.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v12\v0\MigrateInternalTypeRector\MigrateInternalTypeRectorTest
 */
final class MigrateInternalTypeRector extends \Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector
{
    use TcaHelperTrait;
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrates TCA internal_type into new own seperate types', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
'columns' => [
    'aColumn' => [
        'config' => [
            'type' => 'group',
            'internal_type' => 'folder',
        ],
    ],
],
CODE_SAMPLE
, <<<'CODE_SAMPLE'
'columns' => [
    'aColumn' => [
        'config' => [
            'type' => 'folder',
        ],
    ],
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
        if (!$this->configIsOfInternalType($configArray, 'folder')) {
            return;
        }
        $toRemoveArrayItem = $this->extractArrayItemByKey($configArray, 'internal_type');
        if ($toRemoveArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            $this->removeNode($toRemoveArrayItem);
        }
        $toChangeArrayItem = $this->extractArrayItemByKey($configArray, 'type');
        if ($toChangeArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            $toChangeArrayItem->value = new \PhpParser\Node\Scalar\String_('folder');
        }
        $this->hasAstBeenChanged = \true;
    }
}
