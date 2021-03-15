<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

class SkipFunctionArguments
{
    public function run()
    {
        register_shutdown_function([$this, 'shutdown']);
    }

    private function shutdown()
    {
    }
}
