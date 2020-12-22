<?php

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlPConnectToMysqliConnectRector\Fixture;

final class Fixture
{
    public function run($host, $username, $password)
    {
        return mysql_pconnect($host, $username, $password);
    }
}

?>
-----
<?php

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlPConnectToMysqliConnectRector\Fixture;

final class Fixture
{
    public function run($host, $username, $password)
    {
        return mysqli_connect('p:' . $host, $username, $password);
    }
}

?>
