<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class SkipNotIdenticalNoDirectAssignAfterIf
{
    public function f($o)
    {
        if ($o !== null) {

            echo 'STATEMENT HERE';

            $user = $o->user;
        }
    }
}

?>
