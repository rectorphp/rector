<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class SkipNoDirectUsageAfterIf
{
    public function f($o)
    {
        $o2 = $o->mayFail1();
        if ($o2 === null) {
            return null;
        }

        echo 'STATEMENT HERE';

        $mayFail2 = $o2->mayFail2();
        if ($mayFail2 === null) {
            return null;
        }

        echo 'STATEMENT HERE';

        $mayFail3 = $mayFail2->mayFail3();
        if ($mayFail3 === null) {
            return null;
        }

        echo 'STATEMENT HERE';

        $mayFail4 = $mayFail3->mayFail4();
        if ($mayFail4 === null) {
            return null;
        }

        echo 'STATEMENT HERE';

        return $mayFail4->mayFail5();
    }
}

?>
