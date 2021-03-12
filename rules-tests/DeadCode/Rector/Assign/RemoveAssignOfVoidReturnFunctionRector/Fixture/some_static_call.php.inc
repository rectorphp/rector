<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

final class SomeStaticCall
{
    public function run()
    {
        $value = self::someMethod();
    }

    private static function someMethod(): void
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

final class SomeStaticCall
{
    public function run()
    {
        self::someMethod();
    }

    private static function someMethod(): void
    {
    }
}

?>
