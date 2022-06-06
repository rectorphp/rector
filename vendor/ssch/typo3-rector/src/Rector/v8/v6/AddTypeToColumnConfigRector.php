<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v6;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-79440-TcaChanges.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\AddTypeToColumnConfigRector\AddTypeToColumnConfigRectorTest
 */
final class AddTypeToColumnConfigRector extends AbstractTcaRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add type to column config if not exists', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'columns' => [
        'bar' => []
    ]
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'columns' => [
        'bar' => [
            'config' => [
                'type' => 'none'
            ]
        ]
    ]
];
CODE_SAMPLE
)]);
    }
    /**
     * Refactors a single TCA column definition like 'column_name' => [ 'label' => 'column label', 'config' => [], ]
     *
     * remark: checking if the passed nodes really are a TCA snippet must be checked by the caller.
     *
     * @param Expr $columnName the key in above example (typically String_('column_name'))
     * @param Expr $columnTca the value in above example (typically an associative Array with stuff like 'label', 'config', 'exclude', ...)
     */
    protected function refactorColumn(Expr $columnName, Expr $columnTca) : void
    {
        if (!$columnTca instanceof Array_) {
            return;
        }
        $config = null;
        $configItem = $this->extractArrayItemByKey($columnTca, 'config');
        if (null !== $configItem) {
            $config = $configItem->value;
            if (!$config instanceof Array_) {
                return;
            }
        }
        if (null === $config) {
            // found a column without a 'config' part. Create an empty 'config' array
            $config = new Array_();
            $columnTca->items[] = new ArrayItem($config, new String_('config'));
            $this->hasAstBeenChanged = \true;
        }
        if (null === $this->extractArrayItemByKey($config, self::TYPE)) {
            // found a column without a 'type' field in the config. add type => none
            $config->items[] = new ArrayItem(new String_('none'), new String_(self::TYPE));
            $this->hasAstBeenChanged = \true;
        }
    }
    /**
     * We need to weaken the typical constraint regarding column-config detection here, as the configs we are targeting
     * won't contain a 'type' field.
     *
     * @inheritdoc
     */
    protected function isSingleTcaColumn(ArrayItem $arrayItem) : bool
    {
        $labelNode = $this->extractArrayItemByKey($arrayItem->value, 'label');
        return null !== $labelNode;
    }
}
