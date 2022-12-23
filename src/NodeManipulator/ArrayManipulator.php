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
use RectorPrefix202212\Symfony\Contracts\Service\Attribute\Required;
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
    public function __construct(RectorChangeCollector $rectorChangeCollector)
    {
        $this->rectorChangeCollector = $rectorChangeCollector;
    }
    /**
     * @required
     */
    public function autowire(ExprAnalyzer $exprAnalyzer) : void
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function isDynamicArray(Array_ $array) : bool
    {
        foreach ($array->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if (!$this->isAllowedArrayKey($item->key)) {
                return \true;
            }
            if (!$this->isAllowedArrayValue($item->value)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @api symfony
     */
    public function addItemToArrayUnderKey(Array_ $array, ArrayItem $newArrayItem, string $key) : void
    {
        foreach ($array->items as $item) {
            if ($item === null) {
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
    /**
     * @api
     */
    public function findItemInInArrayByKeyAndUnset(Array_ $array, string $keyName) : ?ArrayItem
    {
        foreach ($array->items as $i => $item) {
            if ($item === null) {
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
            $this->rectorChangeCollector->notifyNodeFileInfo($removedArrayItem);
            return $item;
        }
        return null;
    }
    /**
     * @api symfony
     */
    public function hasKeyName(ArrayItem $arrayItem, string $name) : bool
    {
        if (!$arrayItem->key instanceof String_) {
            return \false;
        }
        return $arrayItem->key->value === $name;
    }
    private function isAllowedArrayKey(?Expr $expr) : bool
    {
        if (!$expr instanceof Expr) {
            return \true;
        }
        return \in_array(\get_class($expr), [String_::class, LNumber::class], \true);
    }
    private function isAllowedArrayValue(Expr $expr) : bool
    {
        if ($expr instanceof Array_) {
            return !$this->isDynamicArray($expr);
        }
        return !$this->exprAnalyzer->isDynamicExpr($expr);
    }
}
