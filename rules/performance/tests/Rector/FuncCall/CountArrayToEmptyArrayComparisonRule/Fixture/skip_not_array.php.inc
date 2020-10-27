<?php

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

use Countable;

class SkipNotArray
{
    public function run(Countable $countable)
    {
        count($countable);
    }
}
