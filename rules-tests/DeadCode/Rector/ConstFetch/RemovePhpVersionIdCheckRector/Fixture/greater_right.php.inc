<?php

namespace Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\Fixture;

class GreaterRight
{
    public function run()
    {
        if (80000 > PHP_VERSION_ID) {
            return;
        }

        echo 'do something';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\Fixture;

class GreaterRight
{
    public function run()
    {
        echo 'do something';
    }
}

?>
