<?php

namespace Rector\DeadCode\Tests\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\Fixture;

class CompareToIntegerInversed
{
    public function run()
    {
        // current PHP: 10.0
        if (version_compare('7.2', PHP_VERSION, '<') == 1) {
            return 'is PHP 7.1-';
        } else {
            return 'is PHP 7.2+';
        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\Fixture;

class CompareToIntegerInversed
{
    public function run()
    {
        // current PHP: 10.0
        return 'is PHP 7.2+';
    }
}

?>
