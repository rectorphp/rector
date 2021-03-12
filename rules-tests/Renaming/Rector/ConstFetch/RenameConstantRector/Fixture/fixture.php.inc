<?php

namespace Rector\Renaming\Tests\Rector\ConstFetch\RenameConstantRector\Fixture;

final class Fixture
{
    public function run()
    {
        return MYSQL_ASSOC;
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\ConstFetch\RenameConstantRector\Fixture;

final class Fixture
{
    public function run()
    {
        return MYSQLI_ASSOC;
    }
}

?>
