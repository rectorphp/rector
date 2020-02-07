<?php

namespace Rector\Php72\Tests\Rector\FuncCall\ParseStrWithResultArgumentRector\Fixture;

class AlreadySet
{
    public function run()
    {
        $query = 'bla';
        parse_str($query, $data);
    }
}
