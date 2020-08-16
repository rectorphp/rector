<?php

namespace Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture;

class NormalObject
{
    private static $toBePublicStaticProperty;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture;

class NormalObject
{
    public static $toBePublicStaticProperty;
}

?>
