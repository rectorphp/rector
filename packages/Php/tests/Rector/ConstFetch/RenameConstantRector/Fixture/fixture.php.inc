<?php

namespace Rector\Php\Tests\Rector\ConstFetch\RenameConstantRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return MYSQL_ASSOC;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ConstFetch\RenameConstantRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return MYSQLI_ASSOC;
    }
}

?>
