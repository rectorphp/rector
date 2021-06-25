<?php

namespace Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\Fixture;

class GreaterLeft
{
    public function run()
    {
        if (PHP_VERSION_ID > 80000) {
            echo 'do something';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\Fixture;

class GreaterLeft
{
    public function run()
    {
        echo 'do something';
    }
}

?>
