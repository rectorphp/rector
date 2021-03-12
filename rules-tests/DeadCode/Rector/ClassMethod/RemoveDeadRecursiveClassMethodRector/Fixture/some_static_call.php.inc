<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

class SomeStaticCall
{
    public static function run()
    {
        return self::run();
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

class SomeStaticCall
{
}

?>
