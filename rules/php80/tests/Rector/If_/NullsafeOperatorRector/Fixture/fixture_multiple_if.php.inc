<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureMultipleIf
{
    public function f($o)
    {
        $o2 = $o->mayFail1();
        if ($o2 === null) {
            return null;
        }

        $mayFail2 = $o2->mayFail2();
        if ($mayFail2 === null) {
            return null;
        }

        $mayFail3 = $mayFail2->mayFail3();
        if ($mayFail3 === null) {
            return null;
        }

        $mayFail4 = $mayFail3->mayFail4();
        if ($mayFail4 === null) {
            return null;
        }

        return $mayFail4->mayFail5();
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureMultipleIf
{
    public function f($o)
    {
        return $o->mayFail1()?->mayFail2()?->mayFail3()?->mayFail4()?->mayFail5();
    }
}

?>
