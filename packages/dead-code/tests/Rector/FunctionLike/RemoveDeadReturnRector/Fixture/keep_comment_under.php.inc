<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDeadReturnRector\Fixture;

class KeepCommentUnder
{
    public function run()
    {
        return 'something'; // this comment explains stuff
    }
}
