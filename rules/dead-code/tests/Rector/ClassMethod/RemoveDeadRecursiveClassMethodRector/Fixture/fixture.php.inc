<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

class Fixture
{
}

?>
