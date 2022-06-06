<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v7\v6;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.6/Deprecation-69822-DeprecateSelectFieldTca.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v6\AddRenderTypeToSelectFieldRector\AddRenderTypeToSelectFieldRectorTest
 */
final class AddRenderTypeToSelectFieldRector extends AbstractTcaRector
{
    /**
     * @var string
     */
    private const RENDER_TYPE = 'renderType';
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add renderType for select fields', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'sys_language_uid' => [
            'config' => [
                'type' => 'select',
                'maxitems' => 1,
            ],
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'sys_language_uid' => [
            'config' => [
                'type' => 'select',
                'maxitems' => 1,
                'renderType' => 'selectSingle',
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
    protected function refactorColumn(Expr $columnName, Expr $columnTca) : void
    {
        $configArray = $this->extractSubArrayByKey($columnTca, self::CONFIG);
        if (!$configArray instanceof Array_) {
            return;
        }
        if (!$this->hasKeyValuePair($configArray, self::TYPE, 'select')) {
            return;
        }
        if (null !== $this->extractArrayItemByKey($configArray, self::RENDER_TYPE)) {
            // If the renderType is already set, do nothing
            return;
        }
        $renderModeExpr = $this->extractArrayValueByKey($configArray, 'renderMode');
        if (null !== $renderModeExpr) {
            if ($this->valueResolver->isValue($renderModeExpr, 'tree')) {
                $renderType = 'selectTree';
            } elseif ($this->valueResolver->isValue($renderModeExpr, 'singlebox')) {
                $renderType = 'selectSingleBox';
            } elseif ($this->valueResolver->isValue($renderModeExpr, 'checkbox')) {
                $renderType = 'selectCheckBox';
            } else {
                $this->rectorOutputStyle->warning(\sprintf('The render mode %s is invalid for the select field in %s', $this->valueResolver->getValue($renderModeExpr), $this->valueResolver->getValue($columnName)));
                return;
            }
        } else {
            $maxItemsExpr = $this->extractArrayValueByKey($configArray, 'maxitems');
            $maxItems = null !== $maxItemsExpr ? $this->valueResolver->getValue($maxItemsExpr) : null;
            $renderType = $maxItems <= 1 ? 'selectSingle' : 'selectMultipleSideBySide';
        }
        $renderTypeItem = new ArrayItem(new String_($renderType), new String_(self::RENDER_TYPE));
        $this->insertItemAfterKey($configArray, $renderTypeItem, self::TYPE);
        $this->hasAstBeenChanged = \true;
    }
}
