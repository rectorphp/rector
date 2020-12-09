<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\UnwrapSprintfOneArgumentRector\Fixture;

class SkipMultiArgs
{
    public function run()
    {
        echo sprintf('value %s', '1');
    }
}
