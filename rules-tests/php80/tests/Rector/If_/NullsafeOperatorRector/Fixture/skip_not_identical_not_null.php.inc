<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

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
