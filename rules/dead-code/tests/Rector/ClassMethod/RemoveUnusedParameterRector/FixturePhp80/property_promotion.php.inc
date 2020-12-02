<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\FixturePhp80;

class PropertyPromotion
{
    public function __construct(public int $times, string $name)
    {
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\FixturePhp80;

class PropertyPromotion
{
    public function __construct(public int $times)
    {
    }
}

?>
