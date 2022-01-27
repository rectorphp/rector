<?php

namespace Rector\Core\Tests\Issues\Issue6972\Fixture;

use Exception;

final class Fixture
{
    public function run()
    {
        try {
            $this->doSomething();
        } catch (Exception $throwable) {
            throw $throwable;
        }
    }

    public function doSomething()
    {
    }
}
?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6972\Fixture;

use Exception;

final class Fixture
{
    public function run()
    {
        try {
            $this->doSomething();
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function doSomething()
    {
    }
}
?>
