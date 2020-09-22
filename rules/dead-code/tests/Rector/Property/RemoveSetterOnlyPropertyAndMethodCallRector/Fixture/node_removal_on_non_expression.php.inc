<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class NodeRemovalOnNonExpression
{
    /** @var */
    private $foo;

    public function setFoo($foo)
    {
        return $this->foo = $foo;
    }

    public function whenFoo($foo)
    {
        if($this->foo = $foo) {
            doSomething();
        }
    }

}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class NodeRemovalOnNonExpression
{
    public function setFoo($foo)
    {
        return $foo;
    }

    public function whenFoo($foo)
    {
        if($foo) {
            doSomething();
        }
    }

}

?>
