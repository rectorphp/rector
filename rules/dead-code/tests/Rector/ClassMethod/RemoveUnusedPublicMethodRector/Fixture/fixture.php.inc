<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

final class Fixture
{
    public function unusedpublicMethod()
    {
        echo 'statement';
    }

    public function execute()
    {
        echo 'statement';
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
    public function execute()
    {
        echo 'statement';
    }

    public function run()
    {
        $obj = new self;
        $obj->execute();
    }
}

?>
