<?php

namespace Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\Fixture;

class SmallerRight
{
    public function run()
    {
        if (80000 < PHP_VERSION_ID) {
            echo 'do something';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\Fixture;

class SmallerRight
{
    public function run()
    {
        echo 'do something';
    }
}

?>
