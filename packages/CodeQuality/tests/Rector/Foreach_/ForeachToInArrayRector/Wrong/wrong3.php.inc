<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Wrong;

class SomeClass
{
    public function foreachWithElseNullable()
    {
        foreach ($items as $item) {
            // some comment
            if ($item == 'something') {
                return true;
            }
        }

        return false;
    }
}
