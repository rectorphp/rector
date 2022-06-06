<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v6;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-79440-TcaChanges.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\AddTypeToColumnConfigRector\AddTypeToColumnConfigRectorTest
 */
final class AddTypeToColumnConfigRector extends \Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add type to column config if not exists', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    protected function refactorColumn(\PhpParser\Node\Expr $columnName, \PhpParser\Node\Expr $columnTca) : void
    {
        if (!$columnTca instanceof \PhpParser\Node\Expr\Array_) {
            return;
        }
        $config = null;
        $configItem = $this->extractArrayItemByKey($columnTca, 'config');
        if (null !== $configItem) {
            $config = $configItem->value;
            if (!$config instanceof \PhpParser\Node\Expr\Array_) {
                return;
            }
        }
        if (null === $config) {
            // found a column without a 'config' part. Create an empty 'config' array
            $config = new \PhpParser\Node\Expr\Array_();
            $columnTca->items[] = new \PhpParser\Node\Expr\ArrayItem($config, new \PhpParser\Node\Scalar\String_('config'));
            $this->hasAstBeenChanged = \true;
        }
        if (null === $this->extractArrayItemByKey($config, self::TYPE)) {
            // found a column without a 'type' field in the config. add type => none
            $config->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\String_('none'), new \PhpParser\Node\Scalar\String_(self::TYPE));
            $this->hasAstBeenChanged = \true;
        }
    }
    /**
     * We need to weaken the typical constraint regarding column-config detection here, as the configs we are targeting
     * won't contain a 'type' field.
     *
     * @inheritdoc
     */
    protected function isSingleTcaColumn(\PhpParser\Node\Expr\ArrayItem $arrayItem) : bool
    {
        $labelNode = $this->extractArrayItemByKey($arrayItem->value, 'label');
        return null !== $labelNode;
    }
}
