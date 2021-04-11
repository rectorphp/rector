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
        $result = ($_ = ($_ = $object->multiple($args1)) ? $_->call($args2) : null) ? $_->otherCall($args3) : null;
        $result = ($_ = ($_ = ($_ = $object->multiple($args1)) ? $_->call($args2) : null) ? $_->otherCall($args3) : null) ? $_->anotherCall($args4) : null;
    }
}

?>
