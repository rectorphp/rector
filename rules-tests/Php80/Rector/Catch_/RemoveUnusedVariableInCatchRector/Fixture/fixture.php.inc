<?php

namespace Rector\Tests\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector\Fixture;

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

namespace Rector\Tests\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector\Fixture;

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
