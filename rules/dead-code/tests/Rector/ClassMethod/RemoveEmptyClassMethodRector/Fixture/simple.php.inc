<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

class Simple
{
    public function __construct()
    {
    }

    public function boom()
    {
        // intentionally
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

class Simple
{
    public function boom()
    {
        // intentionally
    }
}

?>
