<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class WithInterfaceSuffix
{
    public function run()
    {
        try {
            $a = 1;
        } catch (ExceptionInterface $test) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class WithInterfaceSuffix
{
    public function run()
    {
        try {
            $a = 1;
        } catch (ExceptionInterface $exception) {
        }
    }
}

?>
