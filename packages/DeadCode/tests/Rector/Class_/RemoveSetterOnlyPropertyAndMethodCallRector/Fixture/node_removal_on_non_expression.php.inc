<?php
namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\node_removal_on_non_expression;

class SomeClass
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
namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\node_removal_on_non_expression;

class SomeClass
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
