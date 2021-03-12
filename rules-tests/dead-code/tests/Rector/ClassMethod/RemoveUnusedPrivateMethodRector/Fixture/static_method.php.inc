<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class StaticMethod
{
    public function run()
    {
        self::skip();
        return 5;
    }

    private static function skip()
    {
        return 10;
    }

    private static function skap()
    {
        return 10;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class StaticMethod
{
    public function run()
    {
        self::skip();
        return 5;
    }

    private static function skip()
    {
        return 10;
    }
}

?>
