<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureNextIsProperty
{
    public function f($o)
    {
        $o2 = $o->property1;
        if ($o2 === null) {
            return null;
        }

        return $o2->property2;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureNextIsProperty
{
    public function f($o)
    {
        return $o->property1?->property2;
    }
}

?>
