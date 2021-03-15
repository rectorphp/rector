<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

class Fixture
{
    public function run()
    {
        return $this->run();
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

class Fixture
{
}

?>
