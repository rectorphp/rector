<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\KeepFunctionCall;

class SomeClass
{
    private $prop;

    public function doThing()
    {
        $this->prop = foo();

        $this->prop[bar()] = foo();

        return $this->prop[bar()] = foo();
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\KeepFunctionCall;

class SomeClass
{
    public function doThing()
    {
        foo();
        bar();
        foo();
        bar();
        return foo();
    }
}

?>
