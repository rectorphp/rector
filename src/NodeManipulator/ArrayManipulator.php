<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
final class ArrayManipulator
{
    /**
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    public function __construct(\Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector)
    {
        $this->rectorChangeCollector = $rectorChangeCollector;
    }
    public function isArrayOnlyScalarValues(\PhpParser\Node\Expr\Array_ $array) : bool
    {
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if ($arrayItem->value instanceof \PhpParser\Node\Expr\Array_) {
                if (!$this->isArrayOnlyScalarValues($arrayItem->value)) {
                    return \false;
                }
                continue;
            }
            if ($arrayItem->value instanceof \PhpParser\Node\Scalar) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    public function addItemToArrayUnderKey(\PhpParser\Node\Expr\Array_ $array, \PhpParser\Node\Expr\ArrayItem $newArrayItem, string $key) : void
    {
        foreach ($array->items as $item) {
            if ($item === null) {
                continue;
            }
            if ($this->hasKeyName($item, $key)) {
                if (!$item->value instanceof \PhpParser\Node\Expr\Array_) {
                    continue;
                }
                $item->value->items[] = $newArrayItem;
                return;
            }
        }
        $array->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\Array_([$newArrayItem]), new \PhpParser\Node\Scalar\String_($key));
    }
    public function findItemInInArrayByKeyAndUnset(\PhpParser\Node\Expr\Array_ $array, string $keyName) : ?\PhpParser\Node\Expr\ArrayItem
    {
        foreach ($array->items as $i => $item) {
            if ($item === null) {
                continue;
            }
            if (!$this->hasKeyName($item, $keyName)) {
                continue;
            }
            $removedArrayItem = $array->items[$i];
            if (!$removedArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            // remove + recount for the printer
            unset($array->items[$i]);
            $this->rectorChangeCollector->notifyNodeFileInfo($removedArrayItem);
            return $item;
        }
        return null;
    }
    public function hasKeyName(\PhpParser\Node\Expr\ArrayItem $arrayItem, string $name) : bool
    {
        if (!$arrayItem->key instanceof \PhpParser\Node\Scalar\String_) {
            return \false;
        }
        return $arrayItem->key->value === $name;
    }
}
