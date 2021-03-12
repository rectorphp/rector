<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

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
