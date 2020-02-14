<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

final class WithParent extends ParentClass
{
    public function run()
    {
    }

    public function trample()
    {
    }
}

class ParentClass
{
    public function run()
    {
        echo 'hi';
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

final class WithParent extends ParentClass
{
    public function run()
    {
    }
}

class ParentClass
{
    public function run()
    {
        echo 'hi';
    }
}

?>
