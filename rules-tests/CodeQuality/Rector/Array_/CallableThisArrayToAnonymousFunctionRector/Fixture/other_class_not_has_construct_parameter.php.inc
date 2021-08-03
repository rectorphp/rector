<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\OtherClass2;

final class OtherClassNotHasConstructParameter
{
    public function run()
    {
        return [OtherClass2::class, 'someMethod'];
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\OtherClass2;

final class OtherClassNotHasConstructParameter
{
    public function run()
    {
        return function () {
            return (new \Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\OtherClass2())->someMethod();
        };
    }
}

?>
