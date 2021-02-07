<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

final class DemoFile
{
    public function notify()
    {
        return 5;
    }

}

final class Caller
{
    public static function execute()
    {
        $demo = new DemoFile();
        $demo->notify();
    }
}
?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

final class DemoFile
{
    public function __invoke()
    {
        return 5;
    }

}

final class Caller
{
    public static function execute()
    {
        $demo = new DemoFile();
        $demo->__invoke();
    }
}
?>
