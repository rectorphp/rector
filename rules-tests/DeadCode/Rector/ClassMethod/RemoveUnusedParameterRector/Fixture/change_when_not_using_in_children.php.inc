<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

abstract class AbstractClass
{
    public abstract function foo(string $value);

}

class ChildClass extends AbstractClass
{
    public function foo(string $value)
    {
        return null;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

abstract class AbstractClass
{
    public abstract function foo();

}

class ChildClass extends AbstractClass
{
    public function foo()
    {
        return null;
    }
}

?>
