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

    public function unusedpublicMethod()
    {
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
