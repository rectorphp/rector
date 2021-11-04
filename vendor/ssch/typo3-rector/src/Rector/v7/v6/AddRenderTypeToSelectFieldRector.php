<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v7\v6;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.6/Deprecation-69822-DeprecateSelectFieldTca.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v6\AddRenderTypeToSelectFieldRector\AddRenderTypeToSelectFieldRectorTest
 */
final class AddRenderTypeToSelectFieldRector extends \Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector
{
    /**
     * @var string
     */
    private const RENDER_TYPE = 'renderType';
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add renderType for select fields', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    protected function refactorColumn(\PhpParser\Node\Expr $columnName, \PhpParser\Node\Expr $columnTca) : void
    {
        $config = $this->extractSubArrayByKey($columnTca, self::CONFIG);
        if (!$config instanceof \PhpParser\Node\Expr\Array_) {
            return;
        }
        if (!$this->hasKeyValuePair($config, self::TYPE, 'select')) {
            return;
        }
        if (null !== $this->extractArrayItemByKey($config, self::RENDER_TYPE)) {
            // If the renderType is already set, do nothing
            return;
        }
        $renderModeExpr = $this->extractArrayValueByKey($config, 'renderMode');
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
            $maxItemsExpr = $this->extractArrayValueByKey($config, 'maxitems');
            $maxItems = null !== $maxItemsExpr ? $this->valueResolver->getValue($maxItemsExpr) : null;
            $renderType = $maxItems <= 1 ? 'selectSingle' : 'selectMultipleSideBySide';
        }
        $renderTypeItem = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\String_($renderType), new \PhpParser\Node\Scalar\String_(self::RENDER_TYPE));
        $this->insertItemAfterKey($config, $renderTypeItem, self::TYPE);
        $this->hasAstBeenChanged = \true;
    }
}
