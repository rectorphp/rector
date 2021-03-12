<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureYoda
{
    public function f($o)
    {
        $o2 = $o->mayFail1();
        if (null === $o2) {
            return null;
        }

        return $o2->mayFail2();
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureYoda
{
    public function f($o)
    {
        return $o->mayFail1()?->mayFail2();
    }
}

?>
