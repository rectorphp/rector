<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Wrong;

class SomeClass
{
    public function foreachWithElseNullable()
    {
        // some comment
        return in_array('something', $items);
    }
}
