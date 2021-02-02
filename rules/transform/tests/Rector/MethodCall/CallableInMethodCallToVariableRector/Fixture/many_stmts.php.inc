<?php

namespace Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Fixture;

use Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Source\DummyCache;

final class ManyStmts
{
    public function run(DummyCache $dummyCache, $someValue)
    {
        $dummyCache->save('key', function ($someValue) {
            $someValue= $someValue + 1000;
            return $someValue + 10;
        });
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Fixture;

use Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Source\DummyCache;

final class ManyStmts
{
    public function run(DummyCache $dummyCache, $someValue)
    {
        $someValue= $someValue + 1000;
        $result = $someValue + 10;
        $dummyCache->save('key', $result);
    }
}

?>
