<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

use Throwable;

final class MoreThanOneTryCatch
{
    public function run()
    {
        try {
        } catch (Throwable $exception) {
        }
        try {
        } catch (Throwable $exception) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

use Throwable;

final class MoreThanOneTryCatch
{
    public function run()
    {
        try {
        } catch (Throwable $throwable) {
        }
        try {
        } catch (Throwable $throwable) {
        }
    }
}

?>
