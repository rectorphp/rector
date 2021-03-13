<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class NoUsageInAnonymousClass
{
    private $var;
    public function foo()
    {
        $this->var = 'used';
        $anonymousClass = new class
        {
            private $var;
        };
        return $this->var;
    }
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class NoUsageInAnonymousClass
{
    private $var;
    public function foo()
    {
        $this->var = 'used';
        $anonymousClass = new class
        {
        };
        return $this->var;
    }
}
?>
