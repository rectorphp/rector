<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class SameClassNotHasConstructParameter
{
    public function __construct()
    {
    }

    public function run()
    {
        return [SameClassNotHasConstructParameter::class, 'someMethod'];
    }

    public function someMethod()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class SameClassNotHasConstructParameter
{
    public function __construct()
    {
    }

    public function run()
    {
        return function () {
            return (new \Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture\SameClassNotHasConstructParameter())->someMethod();
        };
    }

    public function someMethod()
    {
    }
}

?>
