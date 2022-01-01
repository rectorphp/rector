<?php

namespace Rector\Core\Tests\Issues\IssueDowngradeThrowableType\Fixture;

use Throwable;

final class ArrowFunctionReturnThrowable
{
    public function run()
    {
        $value = fn () : ?Throwable => new \Exception('Yikes!');
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\IssueDowngradeThrowableType\Fixture;

use Throwable;

final class ArrowFunctionReturnThrowable
{
    public function run()
    {
        $value = function () {
            return new \Exception('Yikes!');
        };
    }
}

?>
