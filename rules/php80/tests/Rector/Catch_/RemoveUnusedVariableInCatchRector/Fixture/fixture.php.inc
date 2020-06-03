<?php

namespace Rector\Php80\Tests\Rector\Catch_\RemoveUnusedVariableInCatchRector\Fixture;

final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable $notUsedThrowable) {
        }
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Catch_\RemoveUnusedVariableInCatchRector\Fixture;

final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable) {
        }
    }
}

?>
