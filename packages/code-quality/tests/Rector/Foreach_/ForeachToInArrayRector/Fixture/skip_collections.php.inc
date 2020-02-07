<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Source\Collection;

final class SkipCollections
{
    public function foreachToInArray(): bool
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ($item == 'something') {
                return true;
            }
        }

        return false;
    }

    public function foreachToInArrayYoda(): bool
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ('something' == $item) {
                return true;
            }
        }

        return false;
    }
}
