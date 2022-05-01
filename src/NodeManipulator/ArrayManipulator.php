<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use RectorPrefix20220501\Symfony\Contracts\Service\Attribute\Required;
final class ArrayManipulator
{
    /**
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    public function __construct(\Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector)
    {
        $this->rectorChangeCollector = $rectorChangeCollector;
    }
    /**
     * @required
     */
    public function autowire(\Rector\Core\NodeAnalyzer\ExprAnalyzer $exprAnalyzer) : void
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function isDynamicArray(\PhpParser\Node\Expr\Array_ $array) : bool
    {
        foreach ($array->items as $item) {
            if (!$item instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $key = $item->key;
            if (!$this->isAllowedArrayKey($key)) {
                return \true;
            }
            $value = $item->value;
            if (!$this->isAllowedArrayValue($value)) {
                return \true;
            }
        }
        return \false;
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
    private function isAllowedArrayKey(?\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr) {
            return \true;
        }
        return \in_array(\get_class($expr), [\PhpParser\Node\Scalar\String_::class, \PhpParser\Node\Scalar\LNumber::class], \true);
    }
    private function isAllowedArrayValue(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\Array_) {
            return !$this->isDynamicArray($expr);
        }
        return !$this->exprAnalyzer->isDynamicExpr($expr);
    }
}
