<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

class PrivateToo
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

class PrivateToo
{
}

?>
