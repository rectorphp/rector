<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v6;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\ArrayUtility;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-79440-TcaChanges.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\RefactorTCARector\RefactorTCARectorTest
 */
final class RefactorTCARector extends AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var array<string, string>
     */
    private const MAP_WIZARD_TO_FIELD_CONTROL = ['edit' => 'editPopup', 'add' => 'addRecord', 'list' => 'listModule', 'link' => 'linkPopup', 'RTE' => 'fullScreenRichtext'];
    /**
     * @var array<string, string>
     */
    private const MAP_WIZARD_TO_RENDER_TYPE = ['table' => 'textTable', 'colorChoice' => 'colorpicker', 'link' => 'inputLink'];
    /**
     * @var array<string, string>
     */
    private const MAP_WIZARD_TO_CUSTOM_TYPE = ['select' => 'valuePicker', 'suggest' => 'suggestOptions', 'angle' => 'slider'];
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
        $columns = $this->extractColumns($node);
        if (!$columns instanceof ArrayItem) {
            return null;
        }
        $items = $columns->value;
        if (!$items instanceof Array_) {
            return null;
        }
        $this->addFieldControlInsteadOfWizardsAddListEdit($items);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('A lot of different TCA changes', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'text_17' => [
            'label' => 'text_17',
            'config' => [
                'type' => 'text',
                'cols' => '40',
                'rows' => '5',
                'wizards' => [
                    'table' => [
                        'notNewRecords' => 1,
                        'type' => 'script',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:bodytext.W.table',
                        'icon' => 'content-table',
                        'module' => [
                            'name' => 'wizard_table'
                        ],
                        'params' => [
                            'xmlOutput' => 0
                        ]
                    ],
                ],
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
        'text_17' => [
            'label' => 'text_17',
            'config' => [
                'type' => 'text',
                'cols' => '40',
                'rows' => '5',
                'renderType' => 'textTable',
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
    private function addFieldControlInsteadOfWizardsAddListEdit(Array_ $itemsArray) : void
    {
        foreach ($itemsArray->items as $fieldValue) {
            if (!$fieldValue instanceof ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
                continue;
            }
            $fieldName = $this->valueResolver->getValue($fieldValue->key);
            if (null === $fieldName) {
                continue;
            }
            if (!$fieldValue->value instanceof Array_) {
                continue;
            }
            $fieldValueArray = $fieldValue->value;
            foreach ($fieldValueArray->items as $configValue) {
                if (!$configValue instanceof ArrayItem) {
                    continue;
                }
                if (!$configValue->value instanceof Array_) {
                    continue;
                }
                /** @var Array_ $configValueArray */
                $configValueArray = $configValue->value;
                // Refactor input type
                if ($this->isConfigType($configValueArray, 'input') && !$this->hasRenderType($configValueArray)) {
                    $this->refactorRenderTypeInputDateTime($configValue);
                }
                foreach ($configValueArray->items as $configItemValue) {
                    if (!$configItemValue instanceof ArrayItem) {
                        continue;
                    }
                    if (null === $configItemValue->key) {
                        continue;
                    }
                    if (!$this->valueResolver->isValues($configItemValue->key, ['wizards'])) {
                        continue;
                    }
                    if (!$configItemValue->value instanceof Array_) {
                        continue;
                    }
                    $fieldControl = [];
                    $customTypeOptions = [];
                    $remainingWizards = \count($configItemValue->value->items);
                    foreach ($configItemValue->value->items as $wizardItemValue) {
                        if (!$wizardItemValue instanceof ArrayItem) {
                            continue;
                        }
                        if (!$wizardItemValue->key instanceof Expr) {
                            continue;
                        }
                        /** @var Expr $wizardItemValueKey */
                        $wizardItemValueKey = $wizardItemValue->key;
                        $validWizard = $this->isValidWizard($wizardItemValue);
                        if ($validWizard || Strings::startsWith($this->valueResolver->getValue($wizardItemValueKey), '_')) {
                            --$remainingWizards;
                        }
                        if (!$validWizard) {
                            continue;
                        }
                        $this->removeNode($wizardItemValue);
                        if (!$wizardItemValue->value instanceof Array_) {
                            continue;
                        }
                        $wizardItemValueKey = $this->valueResolver->getValue($wizardItemValueKey);
                        if (null === $wizardItemValueKey) {
                            continue;
                        }
                        $fieldControlKey = null;
                        if (\array_key_exists($wizardItemValueKey, self::MAP_WIZARD_TO_FIELD_CONTROL)) {
                            $fieldControlKey = self::MAP_WIZARD_TO_FIELD_CONTROL[$wizardItemValueKey];
                            if ('link' !== $wizardItemValueKey) {
                                $fieldControl[$fieldControlKey] = ['disabled' => \false];
                            }
                        }
                        if (\array_key_exists($wizardItemValueKey, self::MAP_WIZARD_TO_RENDER_TYPE) && null === $this->extractArrayItemByKey($configValueArray, 'renderType')) {
                            $configValueArray->items[] = new ArrayItem(new String_(self::MAP_WIZARD_TO_RENDER_TYPE[$wizardItemValueKey]), new String_('renderType'));
                        }
                        $selectOptions = [];
                        foreach ($wizardItemValue->value->items as $wizardItemSubValue) {
                            if (!$wizardItemSubValue instanceof ArrayItem) {
                                continue;
                            }
                            if (null === $wizardItemSubValue->key) {
                                continue;
                            }
                            // Configuration of slider wizard
                            if ('angle' === $wizardItemValueKey && !$this->valueResolver->isValue($wizardItemSubValue->key, 'type')) {
                                $sliderValue = $this->valueResolver->getValue($wizardItemSubValue->value);
                                if ($sliderValue) {
                                    $customTypeOptions[$this->valueResolver->getValue($wizardItemSubValue->key)] = $sliderValue;
                                }
                            } elseif ('select' === $wizardItemValueKey && $this->valueResolver->isValue($wizardItemSubValue->key, 'items')) {
                                $selectOptions[$this->getValue($wizardItemSubValue->key)] = $this->getValue($wizardItemSubValue->value);
                            }
                            if ($wizardItemSubValue->value instanceof Array_ && $this->valueResolver->isValue($wizardItemSubValue->key, 'params')) {
                                foreach ($wizardItemSubValue->value->items as $paramsValue) {
                                    if (!$paramsValue instanceof ArrayItem) {
                                        continue;
                                    }
                                    if (null === $paramsValue->key) {
                                        continue;
                                    }
                                    $value = $this->valueResolver->getValue($paramsValue->value);
                                    if (null === $value) {
                                        continue;
                                    }
                                    if (null !== $fieldControlKey && $this->valueResolver->isValues($paramsValue->key, ['table', 'pid', 'setValue', 'blindLinkOptions', 'JSopenParams', 'blindLinkFields', 'allowedExtensions'])) {
                                        $paramsValueKey = $this->valueResolver->getValue($paramsValue->key);
                                        if (null !== $paramsValueKey) {
                                            if ('JSopenParams' === $paramsValueKey) {
                                                $paramsValueKey = 'windowOpenParameters';
                                            }
                                            $fieldControl[$fieldControlKey]['options'][$paramsValueKey] = $value;
                                        }
                                    }
                                }
                            } elseif (null !== $fieldControlKey && $this->valueResolver->isValue($wizardItemSubValue->key, 'title')) {
                                $value = $this->valueResolver->getValue($wizardItemSubValue->value);
                                if (null === $value) {
                                    continue;
                                }
                                $fieldControl[$fieldControlKey]['options'][$this->valueResolver->getValue($wizardItemSubValue->key)] = $value;
                            }
                        }
                        if ([] !== $selectOptions && null === $this->extractArrayItemByKey($configValueArray, self::MAP_WIZARD_TO_CUSTOM_TYPE['select'])) {
                            $configValueArray->items[] = new ArrayItem($this->nodeFactory->createArray($selectOptions), new String_(self::MAP_WIZARD_TO_CUSTOM_TYPE['select']));
                        }
                        if ([] !== $customTypeOptions && \array_key_exists($wizardItemValueKey, self::MAP_WIZARD_TO_CUSTOM_TYPE) && null === $this->extractArrayItemByKey($configValueArray, self::MAP_WIZARD_TO_CUSTOM_TYPE[$wizardItemValueKey])) {
                            $configValueArray->items[] = new ArrayItem($this->nodeFactory->createArray($customTypeOptions), new String_(self::MAP_WIZARD_TO_CUSTOM_TYPE[$wizardItemValueKey]));
                        }
                    }
                    $existingFieldControl = $this->extractArrayItemByKey($configValueArray, 'fieldControl');
                    if (null === $existingFieldControl && [] !== $fieldControl) {
                        $configValueArray->items[] = new ArrayItem($this->nodeFactory->createArray($fieldControl), new String_('fieldControl'));
                    } elseif ([] !== $fieldControl && $existingFieldControl instanceof ArrayItem) {
                        foreach ($fieldControl as $fieldControlKey => $fieldControlValue) {
                            if (null !== $this->extractArrayItemByKey($existingFieldControl->value, $fieldControlKey)) {
                                continue;
                            }
                            if (!$existingFieldControl->value instanceof Array_) {
                                continue;
                            }
                            $existingFieldControl->value->items[] = new ArrayItem($this->nodeFactory->createArray($fieldControlValue), new String_($fieldControlKey));
                        }
                    }
                    if (0 === $remainingWizards) {
                        $this->removeNode($configItemValue);
                    }
                }
            }
        }
    }
    private function refactorRenderTypeInputDateTime(ArrayItem $configValueArrayItem) : void
    {
        if (!$configValueArrayItem->value instanceof Array_) {
            return;
        }
        foreach ($configValueArrayItem->value->items as $configItemValue) {
            if (!$configItemValue instanceof ArrayItem) {
                continue;
            }
            if (null === $configItemValue->key) {
                continue;
            }
            if (!$this->valueResolver->isValue($configItemValue->key, 'eval')) {
                continue;
            }
            $eval = $this->valueResolver->getValue($configItemValue->value);
            if (null === $eval) {
                continue;
            }
            $eval = ArrayUtility::trimExplode(',', $eval, \true);
            if (\in_array('date', $eval, \true) || \in_array('datetime', $eval, \true) || \in_array('time', $eval, \true) || \in_array('timesec', $eval, \true)) {
                $configValueArrayItem->value->items[] = new ArrayItem(new String_('inputDateTime'), new String_('renderType'));
            }
        }
    }
    /**
     * @param mixed $wizardItemValue
     */
    private function isValidWizard($wizardItemValue) : bool
    {
        return $this->valueResolver->isValues($wizardItemValue->key, \array_merge(\array_keys(self::MAP_WIZARD_TO_FIELD_CONTROL), \array_keys(self::MAP_WIZARD_TO_RENDER_TYPE), \array_keys(self::MAP_WIZARD_TO_CUSTOM_TYPE)));
    }
}
