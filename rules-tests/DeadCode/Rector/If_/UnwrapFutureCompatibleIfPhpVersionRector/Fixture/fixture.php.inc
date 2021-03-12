<?php

namespace Rector\Tests\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\Fixture;

class Fixture
{
    public function run()
    {
        // current PHP: 7.2
        if (version_compare(PHP_VERSION, '7.2', '<')) {
            return 'is PHP 7.1-';
        } else {
            return 'is PHP 7.2+';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\Fixture;

class Fixture
{
    public function run()
    {
        // current PHP: 7.2
        return 'is PHP 7.2+';
    }
}

?>
