<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

use Throwable;

final class MoreThanOneMethodContainsTryCatch
{
    public function run()
    {
        try {
        } catch (Throwable $exception) {
        }
    }

    public function more()
    {
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

final class MoreThanOneMethodContainsTryCatch
{
    public function run()
    {
        try {
        } catch (Throwable $throwable) {
        }
    }

    public function more()
    {
        try {
        } catch (Throwable $throwable) {
        }
    }
}

?>
