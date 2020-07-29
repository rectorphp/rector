<?php

namespace Rector\Generic\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Fixture;

class NormalObject
{
    private static $toBePublicStaticProperty;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Fixture;

class NormalObject
{
    public static $toBePublicStaticProperty;
}

?>
