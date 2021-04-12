<?php

namespace Rector\Tests\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\Fixture;

class MultipleCall
{
    public function run($object)
    {
        $result = $object->multiple($args1)?->call($args2)?->otherCall($args3);
        $result = $object->multiple($args1)?->call($args2)?->otherCall($args3)?->anotherCall($args4);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\Fixture;

class MultipleCall
{
    public function run($object)
    {
        $result = ($call = ($multiple = $object->multiple($args1)) ? $multiple->call($args2) : null) ? $call->otherCall($args3) : null;
        $result = ($otherCall = ($call = ($multiple = $object->multiple($args1)) ? $multiple->call($args2) : null) ? $call->otherCall($args3) : null) ? $otherCall->anotherCall($args4) : null;
    }
}

?>
