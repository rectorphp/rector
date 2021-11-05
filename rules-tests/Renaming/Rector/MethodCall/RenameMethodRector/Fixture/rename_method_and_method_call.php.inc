<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source;

final class CustomType
{
    public function notify(): int
    {
        return 5;
    }
}

final class Caller
{
    public static function execute(): void
    {
        $demo = new CustomType();
        $demo->notify();
    }
}
?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source;

final class CustomType
{
    public function __invoke(): int
    {
        return 5;
    }
}

final class Caller
{
    public static function execute(): void
    {
        $demo = new CustomType();
        $demo->__invoke();
    }
}
?>
