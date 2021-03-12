<?php

namespace Rector\Tests\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector\Fixture;

class SkipAlreadySet
{
    public function run()
    {
        $query = 'bla';
        parse_str($query, $data);
    }
}
