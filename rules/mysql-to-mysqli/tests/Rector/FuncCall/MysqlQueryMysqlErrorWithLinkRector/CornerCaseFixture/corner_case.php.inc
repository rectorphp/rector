<?php

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector\Fixture;

class CornerCase
{
    public function foo()
    {
        $row['ttl'] = 7_200;
    }

    public function bar()
    {
        return array_flip([]);
    }
}

