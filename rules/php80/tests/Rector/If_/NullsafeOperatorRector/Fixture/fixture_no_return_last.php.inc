<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureNoReturnLast
{
    public function f($o)
    {
        $o2 = $o->mayFail1();
        if ($o2 === null) {
            return null;
        }

        $o2->mayFail2();
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureNoReturnLast
{
    public function f($o)
    {
        $o->mayFail1()?->mayFail2();
    }
}

?>
