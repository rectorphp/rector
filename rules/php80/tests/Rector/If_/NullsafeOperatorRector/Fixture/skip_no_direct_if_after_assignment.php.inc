<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class SkipNoDirectIfAfterAssignment
{
    public function f($o)
    {
        $o2 = $o->mayFail1();
        echo 'statement';
        if ($o2 === null) {
            return null;
        }

        $mayFail2 = $o2->mayFail2();
        echo 'statement';
        if ($mayFail2 === null) {
            return null;
        }

        $mayFail3 = $mayFail2->mayFail3();
        echo 'statement';
        if ($mayFail3 === null) {
            return null;
        }

        $mayFail4 = $mayFail3->mayFail4();
        echo 'statement';
        if ($mayFail4 === null) {
            return null;
        }

        return $mayFail4->mayFail5();
    }
}

?>
