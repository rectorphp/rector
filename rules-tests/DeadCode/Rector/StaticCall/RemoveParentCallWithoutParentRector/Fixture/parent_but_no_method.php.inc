<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class ParentButNoMethod extends ParentMethod
{
    public function run()
    {
        parent::run();
    }

    public function skip()
    {
        parent::skip();
    }

}

class ParentMethod
{
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class ParentButNoMethod extends ParentMethod
{
    public function run()
    {
        parent::run();
    }

    public function skip()
    {
    }

}

class ParentMethod
{
    public function run()
    {
    }
}

?>
