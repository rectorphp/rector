<?php

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector\Fixture;

class PropertyConnection
{
    private $conn;

    public function run()
    {
        $this->conn = mysqli_connect('host', 'user', 'pass');

        mysql_error();
        $sql = 'SELECT';

        return mysql_query($sql);
    }
}

?>
-----
<?php

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector\Fixture;

class PropertyConnection
{
    private $conn;

    public function run()
    {
        $this->conn = mysqli_connect('host', 'user', 'pass');

        mysqli_error($this->conn);
        $sql = 'SELECT';

        return mysqli_query($this->conn, $sql);
    }
}

?>
