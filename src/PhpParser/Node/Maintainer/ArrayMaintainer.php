<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;

final class ArrayMaintainer
{
    public function addItemToArrayUnderKey(Array_ $arrayNode, ArrayItem $newArrayItem, string $key): void
    {
        foreach ($arrayNode->items as $item) {
            if ($this->hasKeyName($item, $key)) {
                if (! $item->value instanceof Array_) {
                    continue;
                }

                $item->value->items[] = $newArrayItem;
                return;
            }
        }

        $arrayNode->items[] = new ArrayItem(new Array_([$newArrayItem]), new String_($key));
    }

    public function findItemInInArrayByKeyAndUnset(Array_ $arrayNode, string $keyName): ?ArrayItem
    {
        foreach ($arrayNode->items as $i => $item) {
            if (! $this->hasKeyName($item, $keyName)) {
                continue;
            }

            // remove + recount for the printer
            unset($arrayNode->items[$i]);

            return $item;
        }

        return null;
    }

    public function hasKeyName(ArrayItem $arrayItemNode, string $name): bool
    {
        return $arrayItemNode->key instanceof String_ && $arrayItemNode->key->value === $name;
    }
}
