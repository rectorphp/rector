<?php

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector\Fixture;

class ConnectionProvided
{
    public function run()
    {
        $link = mysqli_connect('host', 'user', 'pass');
        $res = mysql_query("INSERT INTO ", $link) or die (mysql_error());
    }
}

?>
-----
<?php

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector\Fixture;

class ConnectionProvided
{
    public function run()
    {
        $link = mysqli_connect('host', 'user', 'pass');
        $res = mysqli_query($link, "INSERT INTO ") or die (mysqli_error($link));
    }
}

?>
