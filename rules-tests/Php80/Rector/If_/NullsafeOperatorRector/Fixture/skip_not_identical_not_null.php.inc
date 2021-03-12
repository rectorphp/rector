<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class SkipNotIdenticalNotNull
{
    public function f($o)
    {
        if ($o !== 'abc') {
            $user = $o->user;
        }
    }
}

?>
