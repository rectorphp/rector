<?php

namespace Rector\DowngradePhp80\Tests\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\Fixture;

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

namespace Rector\DowngradePhp80\Tests\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\Fixture;

class MultipleCall
{
    public function run($object)
    {
        $result = ($object->multiple($args1) ? $object->multiple($args1)->call($args2) : null) ? ($object->multiple($args1) ? $object->multiple($args1)->call($args2) : null)->otherCall($args3) : null;
        $result = (($object->multiple($args1) ? $object->multiple($args1)->call($args2) : null) ? ($object->multiple($args1) ? $object->multiple($args1)->call($args2) : null)->otherCall($args3) : null) ? (($object->multiple($args1) ? $object->multiple($args1)->call($args2) : null) ? ($object->multiple($args1) ? $object->multiple($args1)->call($args2) : null)->otherCall($args3) : null)->anotherCall($args4) : null;
    }
}

?>
