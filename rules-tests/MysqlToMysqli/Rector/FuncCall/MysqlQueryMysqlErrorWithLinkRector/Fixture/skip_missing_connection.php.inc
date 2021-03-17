<?php

namespace Rector\Tests\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector\Fixture;

class SkipMissingConnection
{
    public function run()
    {
        mysql_error();
        $sql = 'SELECT';

        return mysql_query($sql);
    }
}
