<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureNotIdenticalYoda
{
    public function f($o)
    {
        if (null !== $o) {
            $user = $o->user;
            if (null !== $user) {
                $address = $user->getAddress();
                if (null !== $address) {
                    $country = $address->getCountry();
                }
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureNotIdenticalYoda
{
    public function f($o)
    {
        $country = $o?->user?->getAddress()?->getCountry();
    }
}

?>
