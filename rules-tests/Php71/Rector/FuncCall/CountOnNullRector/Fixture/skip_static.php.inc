<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

class SkipStatic
{
    public function run()
    {
        static $data = [];
        echo count($data);
    }
}
