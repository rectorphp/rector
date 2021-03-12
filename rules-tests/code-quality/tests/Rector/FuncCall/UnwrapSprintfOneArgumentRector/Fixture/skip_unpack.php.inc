<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\UnwrapSprintfOneArgumentRector\Fixture;

class SkipUnpack
{
    public function run()
    {
        $var = ['value %s', '1'];
        echo sprintf(...$var);
    }
}
