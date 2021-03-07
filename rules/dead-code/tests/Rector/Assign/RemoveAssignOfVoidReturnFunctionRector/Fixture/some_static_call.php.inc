<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

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
