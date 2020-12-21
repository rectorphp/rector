<?php

namespace Rector\CodeQuality\Tests\Rector\Catch_\ThrowWithPreviousExceptionRector\Fixture;

class Fixture
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException('ups');
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Catch_\ThrowWithPreviousExceptionRector\Fixture;

class Fixture
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException('ups', $throwable->getCode(), $throwable);
        }
    }
}

?>
