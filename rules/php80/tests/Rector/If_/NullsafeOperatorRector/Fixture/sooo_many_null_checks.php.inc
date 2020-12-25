<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class SoooManyNullChecks
{
    public function f($main)
    {
        $o2 = $main->mayFail1();
        if ($o2 === null) {
            return null;
        }

        $o3 = $o2->mayFail2();
        if (null === $o3) {
            return null;
        }

        $o4 = $o3->mayFail3();
        if (null === $o4) {
            return null;
        }

        return $o4->mayFail4();
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class SoooManyNullChecks
{
    public function f($main)
    {
        return $main->mayFail1()?->mayFail2()?->mayFail3()?->mayFail4();
    }
}

?>
