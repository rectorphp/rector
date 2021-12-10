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
    protected function isFullTca(\PhpParser\Node\Stmt\Return_ $node) : bool
    {
        $ctrl = $this->extractCtrl($node);
        $columns = $this->extractColumns($node);
        return null !== $ctrl && null !== $columns;
    }
    protected function extractArrayItemByKey(?\PhpParser\Node $node, string $key) : ?\PhpParser\Node\Expr\ArrayItem
    {
        if (null === $node) {
            return null;
        }
        if (!$node instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        foreach ($node->items as $item) {
            if (!$item instanceof \PhpParser\Node\Expr\ArrayItem) {
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
    protected function extractSubArrayByKey(?\PhpParser\Node $node, string $key) : ?\PhpParser\Node\Expr\Array_
    {
        if (null === $node) {
            return null;
        }
        $arrayItem = $this->extractArrayItemByKey($node, $key);
        if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $columnItems = $arrayItem->value;
        if (!$columnItems instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        return $columnItems;
    }
    protected function extractArrayValueByKey(?\PhpParser\Node $node, string $key) : ?\PhpParser\Node\Expr
    {
        return ($extractArrayItemByKey = $this->extractArrayItemByKey($node, $key)) ? $extractArrayItemByKey->value : null;
    }
    protected function hasKey(\PhpParser\Node\Expr\Array_ $configValue, string $configKey) : bool
    {
        foreach ($configValue->items as $configItemValue) {
            if (!$configItemValue instanceof \PhpParser\Node\Expr\ArrayItem) {
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
    protected function hasKeyValuePair(\PhpParser\Node\Expr\Array_ $configValue, string $configKey, string $expectedValue) : bool
    {
        foreach ($configValue->items as $configItemValue) {
            if (!$configItemValue instanceof \PhpParser\Node\Expr\ArrayItem) {
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
    private function isInlineType(\PhpParser\Node\Expr\Array_ $columnItemConfiguration) : bool
    {
        return $this->isConfigType($columnItemConfiguration, 'inline');
    }
    private function isConfigType(\PhpParser\Node\Expr\Array_ $columnItemConfiguration, string $type) : bool
    {
        return $this->hasKeyValuePair($columnItemConfiguration, 'type', $type);
    }
    private function hasRenderType(\PhpParser\Node\Expr\Array_ $columnItemConfiguration) : bool
    {
        $renderTypeItem = $this->extractArrayItemByKey($columnItemConfiguration, 'renderType');
        return null !== $renderTypeItem;
    }
    private function extractColumns(\PhpParser\Node\Stmt\Return_ $node) : ?\PhpParser\Node\Expr\ArrayItem
    {
        return $this->extractArrayItemByKey($node->expr, 'columns');
    }
    private function extractTypes(\PhpParser\Node\Stmt\Return_ $node) : ?\PhpParser\Node\Expr\ArrayItem
    {
        return $this->extractArrayItemByKey($node->expr, 'types');
    }
    private function extractCtrl(\PhpParser\Node\Stmt\Return_ $node) : ?\PhpParser\Node\Expr\ArrayItem
    {
        return $this->extractArrayItemByKey($node->expr, 'ctrl');
    }
    private function extractInterface(\PhpParser\Node\Stmt\Return_ $node) : ?\PhpParser\Node\Expr\ArrayItem
    {
        return $this->extractArrayItemByKey($node->expr, 'interface');
    }
    /**
     * @return Generator<ArrayItem>
     */
    private function extractSubArraysWithArrayItemMatching(\PhpParser\Node\Expr\Array_ $array, string $key, string $value) : \Generator
    {
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (!$arrayItem->value instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            if (!$this->hasKeyValuePair($arrayItem->value, $key, $value)) {
                continue;
            }
            (yield $arrayItem);
        }
        return null;
    }
    private function configIsOfInternalType(\PhpParser\Node\Expr\Array_ $configValue, string $expectedType) : bool
    {
        return $this->hasKeyValuePair($configValue, 'internal_type', $expectedType);
    }
    private function configIsOfRenderType(\PhpParser\Node\Expr\Array_ $configValue, string $expectedRenderType) : bool
    {
        return $this->hasKeyValuePair($configValue, 'renderType', $expectedRenderType);
    }
    /**
     * @return Generator<string, Node>
     */
    private function extractColumnConfig(\PhpParser\Node\Expr\Array_ $items, string $keyName = 'config') : \Generator
    {
        foreach ($items->items as $columnConfig) {
            if (!$columnConfig instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $columnConfig->key) {
                continue;
            }
            $columnName = $this->getValue($columnConfig->key);
            if (null === $columnName) {
                continue;
            }
            if (!$columnConfig->value instanceof \PhpParser\Node\Expr\Array_) {
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
    private function isValue(\PhpParser\Node\Expr $expr, $value) : bool
    {
        return $this->valueResolver->isValue($expr, $value);
    }
    /**
     * @return mixed|null
     */
    private function getValue(\PhpParser\Node\Expr $expr, bool $resolvedClassReference = \false)
    {
        return $this->valueResolver->getValue($expr, $resolvedClassReference);
    }
}
