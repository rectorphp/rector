<?php

namespace Rector\Php\Tests\Rector\FuncCall\ParseStrWithResultArgumentRector\Fixture;

class AlreadySet
{
    public function run()
    {
        $query = 'bla';
        parse_str($query, $data);
    }
}
