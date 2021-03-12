<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

use stdClass;

class SkipSpreadyArrayMerge
{
    public function run()
    {
        $values = [
            [new stdClass()],
            [new stdClass]
        ];

        $items = array_merge(...$values);

        return array_merge($items, ...$values);
    }
}
