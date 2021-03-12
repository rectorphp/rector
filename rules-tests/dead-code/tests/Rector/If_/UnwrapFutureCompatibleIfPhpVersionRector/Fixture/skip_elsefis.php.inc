<?php

namespace Rector\DeadCode\Tests\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\Fixture;

class SkipElsefis
{
    public function run()
    {
        // current PHP: 10.0
        if (1 == version_compare(PHP_VERSION, '7.2', '<')) {
            return 'is PHP 7.1-';
        } elseif (1 == version_compare(PHP_VERSION, '7.3', '<')) {
            return 'hop';
        } else {
            return 'is PHP 7.2+';
        }
    }
}
