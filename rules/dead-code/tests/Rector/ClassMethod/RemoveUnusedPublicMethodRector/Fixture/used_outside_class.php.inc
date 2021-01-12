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

(function() {
    $obj = new UsedOutsideClass();
    $obj->execute();
});
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

(function() {
    $obj = new UsedOutsideClass();
    $obj->execute();
});

?>
