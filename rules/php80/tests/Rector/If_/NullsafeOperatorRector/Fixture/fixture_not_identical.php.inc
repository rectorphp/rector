<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureNotIdentical
{
    public function f($o)
    {
        if ($o !== null) {
            $user = $o->user;
            if ($user !== null) {
                $address = $user->getAddress();
                if ($address !== null) {
                    $country = $address->getCountry();
                }
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureNotIdentical
{
    public function f($o)
    {
        $country = $o?->user?->getAddress()?->getCountry();
    }
}

?>
