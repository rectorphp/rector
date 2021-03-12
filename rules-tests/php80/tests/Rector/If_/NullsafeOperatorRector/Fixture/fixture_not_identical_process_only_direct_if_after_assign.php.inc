<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureNotIdenticalProcessOnlyDirectIfAfterAssign
{
    public function f($o)
    {
        if ($o !== null) {
            $user = $o->user;

            echo 'STATEMENT HERE';

            if ($user !== null) {
                $address = $user->getAddress();

                echo 'STATEMENT HERE';

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

class FixtureNotIdenticalProcessOnlyDirectIfAfterAssign
{
    public function f($o)
    {
        if ($o !== null) {
            $user = $o->user;

            echo 'STATEMENT HERE';

            if ($user !== null) {
                $address = $user->getAddress();

                echo 'STATEMENT HERE';

                $country = $address?->getCountry();
            }
        }
    }
}

?>
