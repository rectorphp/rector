<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

class YesAnotherStaticCall
{
    public static function run()
    {
        return self::run();
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

class YesAnotherStaticCall
{
}

?>
