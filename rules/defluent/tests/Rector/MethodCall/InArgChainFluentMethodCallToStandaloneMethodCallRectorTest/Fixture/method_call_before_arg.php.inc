<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\DummyUserProfile;
use Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\DummyUserProvider;

class SomeClass
{
    public function run(DummyUserProvider $dummyUserProvider, $oldDescription)
    {
        $user = $dummyUserProvider->getDummyUser();

        $this->someMethod(
            (new DummyUserProfile())
                ->setOldDescription($oldDescription)
                ->setUserId($user->id)
        );
    }

    public function someMethod(DummyUserProfile $dummyUserProfile)
    {
    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\DummyUserProfile;
use Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\DummyUserProvider;

class SomeClass
{
    public function run(DummyUserProvider $dummyUserProvider, $oldDescription)
    {
        $user = $dummyUserProvider->getDummyUser();
        $dummyUserProfile = new DummyUserProfile();
        ($dummyUserProfile)
            ->setOldDescription($oldDescription);
        $dummyUserProfile
            ->setUserId($user->id);

        $this->someMethod(
            $dummyUserProfile
        );
    }

    public function someMethod(DummyUserProfile $dummyUserProfile)
    {
    }
}

?>
