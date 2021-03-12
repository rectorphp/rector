<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector\Fixture;

class KeepCommentUnder
{
    public function run()
    {
        return 'something'; // this comment explains stuff
    }
}
