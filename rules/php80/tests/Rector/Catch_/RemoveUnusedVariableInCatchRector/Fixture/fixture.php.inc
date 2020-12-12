<?php

namespace Rector\Php80\Tests\Rector\Catch_\RemoveUnusedVariableInCatchRector\Fixture;

final class Fixture
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

final class Fixture
{
    public function run()
    {
        try {
        } catch (Throwable) {
        }
    }
}

?>
