<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

$data = ['test'];

class SkipGlobal
{
    public function run()
    {
        global $data;
        echo count($data);
    }
}
