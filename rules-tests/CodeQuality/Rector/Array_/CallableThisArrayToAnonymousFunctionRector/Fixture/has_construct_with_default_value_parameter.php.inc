<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\OtherClass3;

final class HasConstructWithDefaultValueParameter
{
    public function run()
    {
        return [OtherClass3::class, 'someMethod'];
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\OtherClass3;

final class HasConstructWithDefaultValueParameter
{
    public function run()
    {
        return function () {
            return (new \Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\OtherClass3())->someMethod();
        };
    }
}

?>
