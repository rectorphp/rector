<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class SkipForwardStaticCall
{
    public function run()
    {
        forward_static_call([$this, 'static_function']);
    }

    public static function static_function()
    {
    }
}
