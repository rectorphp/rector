<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;

final class DemoFile
{
    private function getItemByRefOrNull(?ArrayItem $item, int $condition): ?ArrayItem
    {
        if ($item === null) {
            return null;
        }
        // Check if the item is a nested list/nested array destructuring
        if ($item->value instanceof List_ || $item->value instanceof Array_) {
            // Recursive call
            /** @var List_|Array_ */
            $nestedList = $item->value;
            $hasItemByRef = false;
            if ($condition === self::ALL) {
                $hasItemByRef = $this->hasAllItemsByRef($nestedList->items);
            } elseif ($condition === self::ANY) {
                $hasItemByRef = $this->hasAnyItemByRef($nestedList->items);
            }
            return $hasItemByRef ? $item : null;
        }
        return $item->value instanceof Variable && $item->byRef ? $item : null;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;

final class DemoFile
{
    private function getItemByRefOrNull(?ArrayItem $arrayItem, int $condition): ?ArrayItem
    {
        if ($arrayItem === null) {
            return null;
        }
        // Check if the item is a nested list/nested array destructuring
        if ($arrayItem->value instanceof List_ || $arrayItem->value instanceof Array_) {
            // Recursive call
            /** @var List_|Array_ */
            $nestedList = $arrayItem->value;
            $hasItemByRef = false;
            if ($condition === self::ALL) {
                $hasItemByRef = $this->hasAllItemsByRef($nestedList->items);
            } elseif ($condition === self::ANY) {
                $hasItemByRef = $this->hasAnyItemByRef($nestedList->items);
            }
            return $hasItemByRef ? $arrayItem : null;
        }
        return $arrayItem->value instanceof Variable && $arrayItem->byRef ? $arrayItem : null;
    }
}

?>
