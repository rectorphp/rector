<?php declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Fixture;

final class SetFixture
{
    public function run()
    {
        $connection = mysql_pconnect('one', 'two', 'three');

        mysql_select_db('db_name', $connection);
    }
}

?>
-----
<?php declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Fixture;

final class SetFixture
{
    public function run()
    {
        $connection = mysqli_connect('p:one', 'two', 'three');

        mysqli_select_db($connection, 'db_name');
    }
}

?>
