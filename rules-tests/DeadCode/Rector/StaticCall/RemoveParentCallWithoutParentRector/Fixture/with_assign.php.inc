<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class WithAssign
{
    public function run()
    {
        $result = parent::run();

        return $result;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class WithAssign
{
    public function run()
    {
        $result = null;

        return $result;
    }
}

?>
