<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeManipulator;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
final class ArrayManipulator
{
    public function addItemToArrayUnderKey(Array_ $array, ArrayItem $newArrayItem, string $key) : void
    {
        foreach ($array->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if ($this->hasKeyName($item, $key)) {
                if (!$item->value instanceof Array_) {
                    continue;
                }
                $item->value->items[] = $newArrayItem;
                return;
            }
        }
        $array->items[] = new ArrayItem(new Array_([$newArrayItem]), new String_($key));
    }
    public function findItemInInArrayByKeyAndUnset(Array_ $array, string $keyName) : ?ArrayItem
    {
        foreach ($array->items as $i => $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if (!$this->hasKeyName($item, $keyName)) {
                continue;
            }
            $removedArrayItem = $array->items[$i];
            if (!$removedArrayItem instanceof ArrayItem) {
                continue;
            }
            // remove + recount for the printer
            unset($array->items[$i]);
            return $item;
        }
        return null;
    }
    private function hasKeyName(ArrayItem $arrayItem, string $name) : bool
    {
        if (!$arrayItem->key instanceof String_) {
            return \false;
        }
        return $arrayItem->key->value === $name;
    }
}
