<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

final class RenameClassMethodAndMethodCall
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
        $demo = new RenameClassMethodAndMethodCall();
        $demo->notify();
    }
}
?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

final class RenameClassMethodAndMethodCall
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
        $demo = new RenameClassMethodAndMethodCall();
        $demo->__invoke();
    }
}
?>
