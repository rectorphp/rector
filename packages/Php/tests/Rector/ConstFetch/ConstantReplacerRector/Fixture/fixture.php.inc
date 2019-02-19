<?php

namespace Rector\Php\Tests\Rector\ConstFetch\ConstantReplacerRector\Fixture;

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

namespace Rector\Php\Tests\Rector\ConstFetch\ConstantReplacerRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return MYSQLI_ASSOC;
    }
}

?>
