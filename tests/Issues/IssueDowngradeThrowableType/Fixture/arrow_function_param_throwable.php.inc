<?php

namespace Rector\Core\Tests\Issues\IssueDowngradeThrowableType\Fixture;

use Throwable;

class ArrowFunctionParamThrowable
{
    public function run()
    {
        $value = fn (Throwable $param): bool => true;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\IssueDowngradeThrowableType\Fixture;

use Throwable;

class ArrowFunctionParamThrowable
{
    public function run()
    {
        $value = function ($param) : bool {
            return true;
        };
    }
}

?>
