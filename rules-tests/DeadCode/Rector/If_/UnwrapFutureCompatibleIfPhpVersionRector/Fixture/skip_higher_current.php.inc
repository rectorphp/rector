<?php

namespace Rector\Tests\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\Fixture;

class SkipHigherCurrent
{
    public function run()
    {
        // $this->phpVersionProvider->provide() returns 10000 on test
        if (version_compare(PHP_VERSION, '11.0', '<')) {
            return 'a';
        } else {
            return 'b';
        }
    }
}

?>
