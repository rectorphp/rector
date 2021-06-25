<?php

namespace Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\Fixture;

class SmallerLeft
{
    public function run()
    {
        if (PHP_VERSION_ID < 80000) {
            return;
        }
        echo 'do something';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\Fixture;

class SmallerLeft
{
    public function run()
    {
        echo 'do something';
    }
}

?>
