<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector\FixturePhp80;

class PropertyPromotion
{
    public function __construct(public int $times, string $name)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector\FixturePhp80;

class PropertyPromotion
{
    public function __construct(public int $times)
    {
    }
}

?>
