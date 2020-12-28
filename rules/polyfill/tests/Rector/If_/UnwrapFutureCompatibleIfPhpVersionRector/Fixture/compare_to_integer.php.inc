<?php

namespace Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\Fixture;

class CompareToInteger
{
    public function run()
    {
        // current PHP: 10.0
        if (1 == version_compare(PHP_VERSION, '7.2', '<')) {
            return 'is PHP 7.1-';
        } else {
            return 'is PHP 7.2+';
        }
    }
}

?>
-----
<?php

namespace Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\Fixture;

class CompareToInteger
{
    public function run()
    {
        // current PHP: 10.0
        return 'is PHP 7.2+';
    }
}

?>
