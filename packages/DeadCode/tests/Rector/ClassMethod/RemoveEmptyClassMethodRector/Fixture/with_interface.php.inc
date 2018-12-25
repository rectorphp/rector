<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

class WithInterface implements ImplementedInterface
{
    public function trample()
    {
    }

    public function go()
    {
    }
}

interface ImplementedInterface
{
    public function go();
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

class WithInterface implements ImplementedInterface
{
    public function go()
    {
    }
}

interface ImplementedInterface
{
    public function go();
}

?>
