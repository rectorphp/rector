<?php declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Fixture;

final class SetFixture
{
    public function run()
    {
        mysql_pconnect('one', 'two', 'three');
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
        mysql_pconnect('p:one', 'two', 'three');
    }
}

?>
