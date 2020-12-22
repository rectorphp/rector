<?php

namespace Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture;

class Fixture3
{
    private static $toBePublicStaticProperty;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture;

class Fixture3
{
    public static $toBePublicStaticProperty;
}

?>
