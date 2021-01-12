<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

final class UsedOutsideClass
{
    private function keep()
    {
    }

    public function execute()
    {
    }

    public function unusedpublicMethod()
    {
    }
}

$obj = new UsedOutsideClass();
$obj->execute();

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

final class UsedOutsideClass
{
    private function keep()
    {
    }

    public function execute()
    {
    }
}

$obj = new UsedOutsideClass();
$obj->execute();

?>
