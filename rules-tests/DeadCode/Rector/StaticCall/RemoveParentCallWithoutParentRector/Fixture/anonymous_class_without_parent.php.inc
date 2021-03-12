<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class AnonymousClassWithoutParent
{
    public function foo()
    {
        $class = new class()
        {
            public function foo()
            {
                parent::foo();
            }
        };
    }
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class AnonymousClassWithoutParent
{
    public function foo()
    {
        $class = new class()
        {
            public function foo()
            {
            }
        };
    }
}
?>
