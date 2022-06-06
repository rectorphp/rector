<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97109-TCATypeNoneColsOption.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v12\v0\MigrateColsToSizeForTcaTypeNoneRector\MigrateColsToSizeForTcaTypeNoneRectorTest
 */
final class MigrateColsToSizeForTcaTypeNoneRector extends AbstractTcaRector
{
    use TcaHelperTrait;
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrates option cols to size for TCA type none', [new CodeSample(<<<'CODE_SAMPLE'
'columns' => [
    'aColumn' => [
        'config' => [
            'type' => 'none',
            'cols' => 20,
        ],
    ],
],
CODE_SAMPLE
, <<<'CODE_SAMPLE'
'columns' => [
    'aColumn' => [
        'config' => [
            'type' => 'none',
            'size' => 20,
        ],
    ],
],
CODE_SAMPLE
)]);
    }
    protected function refactorColumn(Expr $columnName, Expr $columnTca) : void
    {
        $configArray = $this->extractSubArrayByKey($columnTca, self::CONFIG);
        if (!$configArray instanceof Array_) {
            return;
        }
        if (!$this->isConfigType($configArray, 'none')) {
            return;
        }
        $arrayItemToChange = $this->extractArrayItemByKey($configArray, 'cols');
        if (!$arrayItemToChange instanceof ArrayItem) {
            return;
        }
        $arrayItemToRemove = $this->extractArrayItemByKey($configArray, 'size');
        if ($arrayItemToRemove instanceof ArrayItem) {
            $this->removeNode($arrayItemToRemove);
        }
        $arrayItemToChange->key = new String_('size');
        $this->hasAstBeenChanged = \true;
    }
}
