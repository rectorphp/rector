<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector\Fixture;

class KeepComment
{
    public function returnStuff()
    {
        return 'something'; // this comment explains stuff
    }
}
