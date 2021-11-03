<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomePgConnect
{
    public function run()
    {
        $dbconn = pg_connect("dbname=mary");
        is_resource($dbconn);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomePgConnect
{
    public function run()
    {
        $dbconn = pg_connect("dbname=mary");
        $dbconn instanceof \PgSql\Connection;
    }
}

?>