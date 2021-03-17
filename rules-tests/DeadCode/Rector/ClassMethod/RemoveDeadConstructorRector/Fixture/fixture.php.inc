<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class Fixture
{
    public function __construct()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class Fixture
{
}

?>
