<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector\Fixture;

class SkipMultiArgs
{
    public function run()
    {
        echo sprintf('value %s', '1');
    }
}
