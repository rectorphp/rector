<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class SkipNativeVariable
{
    public function run()
    {
        $_SESSION = ['foo'];

        check();

        $_SESSION = ['bar'];
    }
}
