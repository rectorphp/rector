<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Fixture;

final class DifferentNameConnectionVariable
{
    public function run()
    {
        $conn = mysql_connect("host", "user", "name");
        mysql_select_db("dbname");
        mysql_query($sql, $conn);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Fixture;

final class DifferentNameConnectionVariable
{
    public function run()
    {
        $conn = mysqli_connect("host", "user", "name");
        mysqli_select_db($conn, "dbname");
        mysqli_query($conn, $sql);
    }
}

?>
