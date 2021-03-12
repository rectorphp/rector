<?php

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector\Fixture;

class Fixture
{
    public function run()
    {
        $conn = mysqli_connect('host', 'user', 'pass');

        mysql_error();
        $sql = 'SELECT';

        return mysql_query($sql);
    }
}

?>
-----
<?php

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector\Fixture;

class Fixture
{
    public function run()
    {
        $conn = mysqli_connect('host', 'user', 'pass');

        mysqli_error($conn);
        $sql = 'SELECT';

        return mysqli_query($conn, $sql);
    }
}

?>
