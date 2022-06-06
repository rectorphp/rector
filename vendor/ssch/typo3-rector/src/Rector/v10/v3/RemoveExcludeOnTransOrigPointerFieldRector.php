<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v3;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.3/Important-89672-TransOrigPointerFieldIsNotLongerAllowedToBeExcluded.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v3\RemoveExcludeOnTransOrigPointerFieldRector\RemoveExcludeOnTransOrigPointerFieldRectorTest
 */
final class RemoveExcludeOnTransOrigPointerFieldRector extends AbstractRector
{
    use TcaHelperTrait;
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isFullTca($node)) {
            return null;
        }
        $ctrlArrayItem = $this->extractCtrl($node);
        if (!$ctrlArrayItem instanceof ArrayItem) {
            return null;
        }
        $ctrlItems = $ctrlArrayItem->value;
        if (!$ctrlItems instanceof Array_) {
            return null;
        }
        $columnsArrayItem = $this->extractColumns($node);
        if (!$columnsArrayItem instanceof ArrayItem) {
            return null;
        }
        $columnItems = $columnsArrayItem->value;
        if (!$columnItems instanceof Array_) {
            return null;
        }
        $transOrigPointerField = null;
        foreach ($ctrlItems->items as $fieldValue) {
            if (!$fieldValue instanceof ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
                continue;
            }
            if ($this->valueResolver->isValue($fieldValue->key, 'transOrigPointerField')) {
                $transOrigPointerField = $this->valueResolver->getValue($fieldValue->value);
                break;
            }
        }
        if (null === $transOrigPointerField) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($columnItems->items as $columnItem) {
            if (!$columnItem instanceof ArrayItem) {
                continue;
            }
            if (null === $columnItem->key) {
                continue;
            }
            $fieldName = $this->valueResolver->getValue($columnItem->key);
            if ($fieldName !== $transOrigPointerField) {
                continue;
            }
            if (!$columnItem->value instanceof Array_) {
                continue;
            }
            foreach ($columnItem->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }
                if (null === $configValue->key) {
                    continue;
                }
                $configFieldName = $this->valueResolver->getValue($configValue->key);
                if ('exclude' === $configFieldName) {
                    $this->removeNode($configValue);
                    $hasAstBeenChanged = \true;
                }
            }
        }
        return $hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('transOrigPointerField is not longer allowed to be excluded', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'transOrigPointerField' => 'l10n_parent',
    ],
    'columns' => [
        'l10n_parent' => [
            'exclude' => true,
            'config' => [
                'type' => 'select',
            ],
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'transOrigPointerField' => 'l10n_parent',
    ],
    'columns' => [
        'l10n_parent' => [
            'config' => [
                'type' => 'select',
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
}
