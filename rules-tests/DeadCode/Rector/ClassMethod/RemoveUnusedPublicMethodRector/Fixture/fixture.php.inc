<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

final class Fixture
{
    public function unusedpublicMethod()
    {
    }

    private function keep()
    {
    }

    public function execute()
    {
    }

    public function run()
    {
        $obj = new self;
        $obj->execute();
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

final class Fixture
{
    private function keep()
    {
    }

    public function execute()
    {
    }

    public function run()
    {
        $obj = new self;
        $obj->execute();
    }
}

?>
