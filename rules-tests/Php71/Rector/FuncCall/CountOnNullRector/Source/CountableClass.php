<?php

declare(strict_types=1);

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Source;
use Countable;

final class CountableClass implements Countable
{
    public function count()
    {
        return 0;
    }
}
