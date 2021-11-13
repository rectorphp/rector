<?php

namespace Rector\Tests\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return (new \SplFixedArray(1))[0];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $object = new \SplFixedArray(1);
        return $object[0];
    }
}

?>
