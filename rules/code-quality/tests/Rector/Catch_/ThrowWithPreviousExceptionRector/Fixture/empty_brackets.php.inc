<?php

namespace Rector\CodeQuality\Tests\Rector\Catch_\ThrowWithPreviousExceptionRector\Fixture;

class EmptyBrackets
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException;
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Catch_\ThrowWithPreviousExceptionRector\Fixture;

class EmptyBrackets
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}

?>
