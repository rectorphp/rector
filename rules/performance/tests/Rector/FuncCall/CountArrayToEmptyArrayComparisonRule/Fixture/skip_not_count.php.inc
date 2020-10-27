<?php

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class SkipNotCount
{
    public function run(array $array)
    {
        empty($array);
    }
}
