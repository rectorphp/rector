<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class MethodArgsMultiCall
{
    public function f($o)
    {
        $o2 = $o->mayFail1('a');
        if ($o2 === null) {
            return null;
        }

        return $o2->mayFail2('b');
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class MethodArgsMultiCall
{
    public function f($o)
    {
        return $o->mayFail1('a')?->mayFail2('b');
    }
}

?>
