<?php

namespace Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Fixture;

use Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Source\DummyCache;

final class SomeClass
{
    public function run(DummyCache $dummyCache)
    {
        $dummyCache->save('key', function () {
            return 100;
        });
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Fixture;

use Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Source\DummyCache;

final class SomeClass
{
    public function run(DummyCache $dummyCache)
    {
        $result = 100;
        $dummyCache->save('key', $result);
    }
}

?>
