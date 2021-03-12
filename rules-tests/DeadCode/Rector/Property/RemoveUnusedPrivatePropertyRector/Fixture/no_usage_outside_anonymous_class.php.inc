<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class NoUsageOutsideAnonymousClass
{
    private $var;
    public function foo()
    {
        return new class
        {
            private $var;
            public function bar(){
                return $this->var;
            }
        };
    }
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class NoUsageOutsideAnonymousClass
{
    public function foo()
    {
        return new class
        {
            private $var;
            public function bar(){
                return $this->var;
            }
        };
    }
}
?>
