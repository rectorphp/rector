<?php

namespace Rector\Php72\Tests\Rector\FuncCall\ParseStrWithResultArgumentRector\Fixture;

class SkipAlreadySet
{
    public function run()
    {
        $query = 'bla';
        parse_str($query, $data);
    }
}
