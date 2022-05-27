<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper;

use Generator;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Return_;
trait TcaHelperTrait
{
    protected function isFullTca(Return_ $return) : bool
    {
        $ctrlArrayItem = $this->extractCtrl($return);
        $columnsArrayItem = $this->extractColumns($return);
        return null !== $ctrlArrayItem && null !== $columnsArrayItem;
    }
    protected function extractArrayItemByKey(?Node $node, string $key) : ?ArrayItem
    {
        if (null === $node) {
            return null;
        }
        if (!$node instanceof Array_) {
            return null;
        }
        foreach ($node->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if (null === $item->key) {
                continue;
            }
            $itemKey = (string) $this->getValue($item->key);
            if ($key === $itemKey) {
                return $item;
            }
        }
        return null;
    }
    protected function extractSubArrayByKey(?Node $node, string $key) : ?Array_
    {
        if (null === $node) {
            return null;
        }
        $arrayItem = $this->extractArrayItemByKey($node, $key);
        if (!$arrayItem instanceof ArrayItem) {
            return null;
        }
        $columnItems = $arrayItem->value;
        if (!$columnItems instanceof Array_) {
            return null;
        }
        return $columnItems;
    }
    protected function extractArrayValueByKey(?Node $node, string $key) : ?Expr
    {
        return ($extractArrayItemByKey = $this->extractArrayItemByKey($node, $key)) ? $extractArrayItemByKey->value : null;
    }
    protected function hasKey(Array_ $configValuesArray, string $configKey) : bool
    {
        foreach ($configValuesArray->items as $configItemValue) {
            if (!$configItemValue instanceof ArrayItem) {
                continue;
            }
            if (null === $configItemValue->key) {
                continue;
            }
            if ($this->isValue($configItemValue->key, $configKey)) {
                return \true;
            }
        }
        return \false;
    }
    protected function hasKeyValuePair(Array_ $configValueArray, string $configKey, string $expectedValue) : bool
    {
        foreach ($configValueArray->items as $configItemValue) {
            if (!$configItemValue instanceof ArrayItem) {
                continue;
            }
            if (null === $configItemValue->key) {
                continue;
            }
            if ($this->isValue($configItemValue->key, $configKey) && $this->isValue($configItemValue->value, $expectedValue)) {
                return \true;
            }
        }
        return \false;
    }
    private function isInlineType(Array_ $columnItemConfigurationArray) : bool
    {
        return $this->isConfigType($columnItemConfigurationArray, 'inline');
    }
    private function isConfigType(Array_ $columnItemConfigurationArray, string $type) : bool
    {
        return $this->hasKeyValuePair($columnItemConfigurationArray, 'type', $type);
    }
    private function hasRenderType(Array_ $columnItemConfigurationArray) : bool
    {
        $renderTypeItem = $this->extractArrayItemByKey($columnItemConfigurationArray, 'renderType');
        return null !== $renderTypeItem;
    }
    private function extractColumns(Return_ $return) : ?ArrayItem
    {
        return $this->extractArrayItemByKey($return->expr, 'columns');
    }
    private function extractTypes(Return_ $return) : ?ArrayItem
    {
        return $this->extractArrayItemByKey($return->expr, 'types');
    }
    private function extractCtrl(Return_ $return) : ?ArrayItem
    {
        return $this->extractArrayItemByKey($return->expr, 'ctrl');
    }
    private function extractInterface(Return_ $return) : ?ArrayItem
    {
        return $this->extractArrayItemByKey($return->expr, 'interface');
    }
    /**
     * @return Generator<ArrayItem>
     */
    private function extractSubArraysWithArrayItemMatching(Array_ $array, string $key, string $value) : Generator
    {
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->value instanceof Array_) {
                continue;
            }
            if (!$this->hasKeyValuePair($arrayItem->value, $key, $value)) {
                continue;
            }
            (yield $arrayItem);
        }
        return null;
    }
    private function configIsOfInternalType(Array_ $configValueArray, string $expectedType) : bool
    {
        return $this->hasKeyValuePair($configValueArray, 'internal_type', $expectedType);
    }
    private function configIsOfRenderType(Array_ $configValueArray, string $expectedRenderType) : bool
    {
        return $this->hasKeyValuePair($configValueArray, 'renderType', $expectedRenderType);
    }
    /**
     * @return Generator<string, Node>
     */
    private function extractColumnConfig(Array_ $array, string $keyName = 'config') : Generator
    {
        foreach ($array->items as $columnConfig) {
            if (!$columnConfig instanceof ArrayItem) {
                continue;
            }
            if (null === $columnConfig->key) {
                continue;
            }
            $columnName = $this->getValue($columnConfig->key);
            if (null === $columnName) {
                continue;
            }
            if (!$columnConfig->value instanceof Array_) {
                continue;
            }
            // search the config sub-array for this field
            foreach ($columnConfig->value->items as $configValue) {
                if (null === (($configValue2 = $configValue) ? $configValue2->key : null)) {
                    continue;
                }
                if (!$this->isValue($configValue->key, $keyName)) {
                    continue;
                }
                (yield $columnName => $configValue->value);
            }
        }
    }
    /**
     * @param mixed $value
     */
    private function isValue(Expr $expr, $value) : bool
    {
        return $this->valueResolver->isValue($expr, $value);
    }
    /**
     * @return mixed|null
     */
    private function getValue(Expr $expr, bool $resolvedClassReference = \false)
    {
        return $this->valueResolver->getValue($expr, $resolvedClassReference);
    }
}
