<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp81\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
final class ArraySpreadAnalyzer
{
    public function isArrayWithUnpack(Array_ $array) : bool
    {
        // Check that any item in the array is the spread
        foreach ($array->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if ($item->unpack) {
                return \true;
            }
        }
        return \false;
    }
}
