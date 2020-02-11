<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\AnonymousClass;

class SomeClass
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

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\AnonymousClass;

class SomeClass
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
