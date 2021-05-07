<?php

namespace Rector\Tests\Renaming\Rector\FuncCall\RenameFunctionRector\Fixture;

class MysqlError
{
    public function run()
    {
        die(mysql_error());
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\FuncCall\RenameFunctionRector\Fixture;

class MysqlError
{
    public function run()
    {
        die(mysqli_error());
    }
}

?>
