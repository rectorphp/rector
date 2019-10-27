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

}
?>
-----
<?php
namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\node_removal_on_non_expression;

class SomeClass
{
    public function setFoo($foo)
    {
    }
}
?>
