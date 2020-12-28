<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class Fixture
{
    public function __construct()
    {
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class Fixture
{
}

?>
