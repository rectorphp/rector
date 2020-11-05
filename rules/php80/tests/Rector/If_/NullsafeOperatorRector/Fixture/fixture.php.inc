<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class SomeClass
{
    public function run()
    {
        $country =  null;

        if ($session !== null) {
            $user = $session->user;

            if ($user !== null) {
                $address = $user->getAddress();

                if ($address !== null) {
                    $country = $address->country;
                }
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\Fixture;

class SomeClass
{
    public function run()
    {
        $country =  null;

        $country = $session?->user?->getAddress()?->country;
    }
}

?>
