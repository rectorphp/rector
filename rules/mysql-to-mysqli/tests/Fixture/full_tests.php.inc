<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Fixture;

final class FullTests
{
    public function run_with_connection()
    {
        $connection = mysql_connect('host', 'user', 'pass', true, MYSQL_CLIENT_IGNORE_SPACE);

        return mysql_close($connection);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Fixture;

final class FullTests
{
    public function run_with_connection()
    {
        $connection = mysqli_connect('host', 'user', 'pass');

        return mysqli_close($connection);
    }
}

?>
