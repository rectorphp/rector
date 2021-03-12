<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector\Fixture;

class SkipUnpack
{
    public function run()
    {
        $var = ['value %s', '1'];
        echo sprintf(...$var);
    }
}
