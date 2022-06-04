<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

use Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Source\AnotherCaller;

final class WithSimleMethodCall
{
    private AnotherCaller $anotherCaller;

    public function __construct(AnotherCaller $anotherCaller)
    {
        $this->anotherCaller = $anotherCaller;
    }

    public function run()
    {
        $anotherCaller = $this->anotherCaller;

        $result = $anotherCaller->callMe();
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

use Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Source\AnotherCaller;

final class WithSimleMethodCall
{
    private AnotherCaller $anotherCaller;

    public function __construct(AnotherCaller $anotherCaller)
    {
        $this->anotherCaller = $anotherCaller;
    }

    public function run()
    {
        $result = $this->anotherCaller->callMe();
    }
}

?>
